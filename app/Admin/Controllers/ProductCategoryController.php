<?php

namespace App\Admin\Controllers;

use App\Models\ProductCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Illuminate\Support\Facades\Log;

class ProductCategoryController extends AdminController
{
    protected $title = '商品分类管理';

    /**
     * 列表页
     */
    protected function grid()
    {
        $grid = new Grid(new ProductCategory());

        $grid->model()->orderBy('id','asc');

        // 筛选器
        $grid->filter(function ($filter) {
            $filter->like('name', '分类名称');
            $categories = ProductCategory::pluck('name', 'id')->prepend('顶级分类', 0);
            $filter->equal('parent_id', '父分类')->select($categories);
        });

        // 列表显示（带层级前缀）
        $grid->column('id', 'ID')->sortable();
        $grid->column('name', '分类名称')->display(function ($name) {
            $level = 0;
            $currentId = $this->parent_id;
            while ($currentId) {
                $level++;
                $parent = ProductCategory::find($currentId);
                $currentId = $parent ? $parent->parent_id : null;
                if ($level > 10) break;
            }
            $prefix = $level > 0 ? '|' . str_repeat('——', $level) : '';
            return $prefix . $name;
        })->sortable();

        // $grid->column('parent.name', '父分类')->default('顶级分类');
        $grid->column('icon', '分类图标')->display(function () {
            // 动态获取应用基础URL，从.env的APP_URL配置，本地是larabbs.test 线上是服务器
            $appBaseUrl = config('app.url');
            #
            $icon =  $this->getImageUrlAttribute();

            if($icon == 'http://larabbs.test/storage/') {
                return '<span class="text-gray">无图片</span>';
            }

            return '<img src="' . e($icon) . '" ' .
            'style="width: 30px; height: 30px; object-fit: cover;" ' .
            'onerror="this.src=\'/images/default-product.png\'; this.onerror=null;">';

            // return $icon ? '<img src="' . $icon . '" style="width:30px;height:30px;">' : '无';
        });
        $grid->column('sort', '权重')->sortable();
        $grid->column('created_at', '创建时间')->sortable();

        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        return $grid;
    }

    /**
     * 表单页（已修复选项键问题）
     */
    protected function form()
    {
        $form = new Form(new ProductCategory());

        $currentId = $form->model()->id ?? null;

        // 分类名称
        $form->text('name', '分类名称')
            ->rules('required|max:50')
            ->placeholder('请输入分类名称');

        // 优化后的修改父分类选择框的排除逻辑
        $form->select('parent_id', '父分类')
        ->options(function () use ($form) {
            // 获取当前编辑的分类ID（新增时为null）
            $currentId = $form->model()->id ?? null;

            // 查询所有分类并按ID索引
            $allCategories = ProductCategory::select('id', 'name', 'parent_id')->get()->keyBy('id');

            // 排除逻辑：当前分类ID及其所有子分类ID
            $excludeIds = [];
            if ($currentId) {
                // 1. 首先排除当前分类自身
                $excludeIds[] = $currentId;

                // 2. 递归获取所有子分类ID并排除
                $getAllChildIds = function ($parentId) use (&$getAllChildIds, $allCategories) {
                    $childIds = [];
                    foreach ($allCategories as $category) {
                        if ($category->parent_id == $parentId) {
                            $childIds[] = $category->id;
                            // 递归获取子分类的子分类
                            $childIds = array_merge($childIds, $getAllChildIds($category->id));
                        }
                    }
                    return $childIds;
                };

                // 将当前分类的所有子分类ID加入排除列表
                $excludeIds = array_merge($excludeIds, $getAllChildIds($currentId));
            }

            // 构建带层级的选项（排除指定ID）
            $options = [0 => '顶级分类'];
            foreach ($allCategories as $id => $category) {
                // 跳过需要排除的ID（当前分类及子分类）
                if (in_array($id, $excludeIds)) {
                    continue;
                }

                // 计算层级
                $level = 0;
                $currentParentId = $category->parent_id;
                // 限制最大层级为10，防止异常循环
                while ($currentParentId && isset($allCategories[$currentParentId]) && $level < 10) {
                    $level++;
                    $currentParentId = $allCategories[$currentParentId]->parent_id;
                }

                // 添加层级前缀
                $prefix = $level > 0 ? '|' . str_repeat('——', $level) : '';
                $options[$id] = $prefix . $category->name;
            }

            return $options;
        })
        ->placeholder('选择父分类')
        ->help('选择父分类创建子分类，留空为顶级分类');


        // 其他字段
        $form->image('icon', '分类图标')
            ->move('categories/icons')
            ->uniqueName()
            ->help('建议尺寸：80x80px');

        $form->textarea('description', '分类描述')
            ->rows(3)
            ->attribute('maxlength', 200)
            ->help('不超过200个字符');

        $form->number('sort', '排序权重')->default(0);

        // 保存验证（增强调试）
        $form->saving(function (Form $form) {
            // 修复：使用 request()->all() 替代 $form->input()
            $parentId = $form->parent_id;

            if ($parentId == 0 || $parentId === null) {
                $form->parent_id = null;
                return;
            }

            // 调试点3：验证父分类存在性
            $parentExists = ProductCategory::where('id', $parentId)->exists();

            if (!$parentExists) {
                throw new \Exception("父分类ID不存在，请重新选择");
            }
        });

        $form->saved(function () {
            admin_toastr('操作成功');
        });

        return $form;
    }
}
