<?php

namespace App\Admin\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Grid\Tools\BatchAction;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProductsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '商品管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Product);

        // 筛选栏
        $grid->filter(function ($filter) {
            // 搜索框
            $filter->like('title', '商品名称');

            // 分类筛选
            $filter->equal('category_id', '商品分类')
                ->select(ProductCategory::pluck('name', 'id'));

            // 上架状态筛选
            $filter->equal('on_sale', '上架状态')
                ->select([
                    1 => '已上架',
                    0 => '未上架'
                ]);

            // 价格区间筛选
            $filter->between('price', '价格区间')->decimal();

            // 热门推荐筛选
            $filter->equal('is_hot', '热门推荐')
                ->select([
                    '' => '全部',
                    1 => '是',
                    0 => '否'
                ]);

            // 新品上市筛选
            $filter->equal('is_new', '新品上市')
                ->select([
                    '' => '全部',
                    1 => '是',
                    0 => '否'
                ]);

            // 限时折扣筛选
            $filter->scope('in_discount', '限时折扣中')->where(function ($query) {
                $query->discount(); // 调用模型中定义的discount()作用域
            });

            $filter->scope('not_discount', '非限时折扣')->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('discount_start_time')
                    ->orWhereNull('discount_end_time')
                    ->orWhere('discount', '<=', 0)
                    ->orWhere('discount_end_time', '<', Carbon::now());
                });
            });
        });

        // 列展示
        $grid->id('ID')->sortable();

        // 商品图片预览
        $grid->image('商品图片')->display(function () {
            $imageUrl =  $this->image_url;
            if($imageUrl == 'http://larabbs.test/storage/') {
                return '<span class="text-gray">无图片</span>';
            }
            return '<img src="' . e($imageUrl) . '" ' .
            'style="width: 50px; height: 50px; object-fit: cover;" ' .
            'onerror="this.src=\'/images/default-product.png\'; this.onerror=null;">';
        });

        $grid->title('商品名称')->limit(30);

        // 关联分类显示
        $grid->category('所属分类')->display(function ($category) {
            return $category ? $category['name'] : '';
        });

        $grid->on_sale('已上架')->display(function ($value) {
            return $value ?
                '<span class="label label-success">是</span>' :
                '<span class="label label-default">否</span>';
        });

        $grid->column('final_price', '最终售价')->display(function() {
            // 查询加载完整模型以触发访问器
            $product = Product::find($this->id);
            return '￥' . number_format($product->final_price, 2);
        });

        // $grid->getFinalPriceAttribute();

        $grid->rating('评分')->sortable();
        $grid->sold_count('销量')->sortable();
        $grid->review_count('评论数')->sortable();

        // 状态显示
        $grid->status('状态')->display(function ($status) {
            $statusMap = [
                1 => '<span class="label label-success">正常</span>',
                2 => '<span class="label label-warning">下架</span>',
                3 => '<span class="label label-info">预售</span>'
            ];
            return $statusMap[$status] ?? '<span class="label label-danger">未知</span>';
        });

        $grid->created_at('创建时间')->sortable();

        // 操作按钮
        $grid->actions(function ($actions) {
            $actions->disableView();
            // 可以根据商品状态显示不同操作
            $product = $actions->row;
            if ($product->on_sale) {
                $actions->append('<a href="javascript:void(0)" class="grid-row-offsale" data-id="'.$product->id.'">下架</a>');
            } else {
                $actions->append('<a href="javascript:void(0)" class="grid-row-onsale" data-id="'.$product->id.'">上架</a>');
            }
        });

        // 批量操作
        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();

                // 批量上架
                $batch->add('批量上架', new class extends BatchAction {
                    // 处理批量操作逻辑
                    public function handle(Collection $ids)
                    {
                        Product::whereIn('id', $ids)->update(['on_sale' => true]);
                        return $this->response()->success('批量上架成功！')->refresh();
                    }

                    // 必须实现的抽象方法（返回空脚本即可）
                    public function script()
                    {
                        return <<<JS
                            // 这里可以添加批量操作的前端确认逻辑
                            $('.grid-batch-actions').on('click', '.batch-action', function() {
                                // 示例：弹出确认框
                                return confirm('确定要执行批量上架操作吗？');
                            });
                        JS;
                    }
                });

                // 批量下架
                $batch->add('批量下架', new class extends BatchAction {
                    public function handle(Collection $ids)
                    {
                        Product::whereIn('id', $ids)->update(['on_sale' => false]);
                        return $this->response()->success('批量下架成功！')->refresh();
                    }

                    public function script()
                    {
                        return <<<JS
                            $('.grid-batch-actions').on('click', '.batch-action', function() {
                                return confirm('确定要执行批量下架操作吗？');
                            });
                        JS;
                    }
                });
            });
        });

        // 每页显示条数
        $grid->perPages([10, 20, 30, 50]);

        return $grid;
    }


    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Product::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('description', __('Description'));
        $show->field('image', __('Image'));
        $show->field('on_sale', __('On sale'));
        $show->field('rating', __('Rating'));
        $show->field('sold_count', __('Sold count'));
        $show->field('review_count', __('Review count'));
        $show->field('price', __('Price'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Product());

        // 基本信息分组（使用 fieldset 替代 card）
        $form->fieldset('基本信息', function ($form) {
            // 商品名称（添加长度限制）
            $form->text('title', '商品名称')
                ->rules('required|max:100')
                ->placeholder('请输入商品名称，不超过100个字符');

            // 所属分类（添加分类选择）
            $form->select('category_id', '所属分类')
                ->options(ProductCategory::selectOptions())
                ->rules('required')
                ->help('请选择商品所属分类');

            // 封面图片（优化提示）
            $form->image('image', '封面图片')
                ->rules('required|image')
                ->move('products/cover')
                ->uniqueName()
                ->help('建议尺寸：800x800px，支持jpg、png格式');

            // 轮播图（新增多图上传）
            $form->multipleImage('images', '轮播图')
                ->move('products/images')
                ->uniqueName()
                ->help('最多上传5张，建议尺寸：1200x600px');

            // 商品描述（优化编辑器配置）
            $form->quill('description', '商品描述')
                ->rules('required|min:10')
                ->options(['height' => 300])
                ->help('请输入详细的商品描述，至少10个字符');

            $form->fieldset('营销标签', function ($form) {
                // 热门推荐
                $form->switch('is_hot', '热门推荐')->default(0)->help('开启后，商品会显示在‘热门推荐’区域');

                // 新品上市
                $form->switch('is_new', '新品上市')->default(0)->help('开启后，商品回显示在"新品上市"区域');

                // 限时则扣
                $form->datetime('discount_start_time', '折扣开始时间')->help('设置折扣生效的开始时间');

                $form->datetime('discount_end_time', '折扣结束时间')->help('设置折扣生效的结束时间');

                $form->decimal('discount', '折扣比例')
                ->default(1.00)
                ->attribute('step', '0.01') // HTML5 step属性限制输入为2位小数
                ->attribute('min', '0.01')  // 最小折扣（1折）
                ->attribute('max', '0.99')  // 最大折扣（不打折）
                ->rules('nullable|numeric|between:0.01,1|regex:/^\d+(\.\d{1,2})?$/')
                ->help('填写0.01-1之间的小数（如0.85表示85折），1表示不打折');


            });

            // 状态选择（细化状态选项）
            $form->radio('status', '商品状态')
                ->options([
                    1 => '正常',
                    2 => '下架',
                    3 => '预售'
                ])
                ->default(1)
                ->help('选择商品当前状态');

            // 上架状态（关联状态逻辑）
            $form->radio('on_sale', '上架状态')
                ->options(['1' => '是', '0' => '否'])
                ->default('0')
                ->help('只有"正常"状态的商品才能上架销售');
        });

        // SEO设置分组
        $form->fieldset('SEO设置', function ($form) {
            // 友好URL标识
            $form->text('slug', 'URL标识')
                ->rules(function ($form) {
                    // 如果是编辑状态，排除当前商品ID
                    $id = $form->model()->id ?? '';
                    return 'nullable|unique:products,slug,$id|regex:/^[a-zA-Z0-9\-_]+&/';
                })
                ->help('用于生成友好的商品URL，只能包含字母、数字、横杠和下划线');
        });

        // SKU信息分组
        $form->fieldset('SKU设置', function ($form) {
            // SKU列表（增强验证和体验）
            $form->hasMany('skus', 'SKU 列表', function (Form\NestedForm $form) {
                $form->text('title', 'SKU 名称')
                    ->rules('required|max:50')
                    ->placeholder('如：红色-XXL');

                $form->text('description', 'SKU 描述')
                    ->rules('required|max:200')
                    ->placeholder('简要描述该规格特点');

                $form->text('price', '单价')
                    ->rules('required|numeric|min:0.01')
                    ->placeholder('0.00')
                    ->attribute('type', 'number')
                    ->attribute('step', '0.01');

                $form->text('stock', '库存数量')
                    ->rules('required|integer|min:0')
                    ->placeholder('0')
                    ->attribute('type', 'number');
            })->required()->help('至少添加一个SKU规格');
        });

        // 保存前的回调（增强逻辑）
        $form->saving(function (Form $form) {
            $discount = $form->discount;
            $startTime = $form->discount_start_time;
            $endTime = $form->discount_end_time;

            // 规则1：如果设置了折扣比例（<1.00) , 必须填写开始和结束时间
            if ($discount && $discount < 1.00) {
                if(empty($startTime) || empty($endTime)) {
                    throw new \Exception('设置折扣比例后，必须填写折扣开始时间和结束时间');
                }

                // 规则2：结束时间必须晚于开始时间
                if(strtotime($endTime) <= strtotime($startTime)) {
                    throw new \Exception('折扣结束时间必须晚于开始时间');
                }
            }
            // 规则3： 如果没有设置折扣比例，清空时间字段
            if (empty($discount) || $discount >= 1.00) {
                $form->discount = null;
                $form->discount_start_time = null;
                $form->discount_end_time = null;
            }



            // // 计算商品最低价格（排除已删除的SKU）
            // $validSkus = collect($form->input('skus'))
            //     ->where(Form::REMOVE_FLAG_NAME, 0)
            //     ->filter();


            // 提取所有SKU的原价，计算最小值并同步到商品price字段
            $validSkus = collect($form->input('skus'))
                ->where(Form::REMOVE_FLAG_NAME, 0)
                ->filter();

            // 首先检查是否是有效的SKU
            if($validSkus->isEmpty()) {
                throw new \Exception('至少小于添加一个SKU规格');
            }
            // 提取所有SKU价格共计算最小值
            $minPrice = $validSkus->pluck('price')
                ->filter() //过滤空值
                ->map(function ($price) {
                    return (float)$price; // 确保转换为空值
                })
                ->min();

            // 检查最低价格是否有效
            if (is_null($minPrice)) {
                throw new \Exception('所有SKU价格不能为空');
            }

            if ($minPrice <= 0) {
                throw new \Exception('商品最低价格必须大于0，请检查SKU价格');
            }

            $form->model()->price = $minPrice;


            // 自动生成slug（如果未填写）
            if (empty($form->slug) && !empty($form->title)) {
                $form->slug = Str::slug($form->title);
            }

            // 状态联动：如果选择下架状态，自动设置为未上架
            if ($form->status == 2) {
                $form->on_sale = 0;
            }
        });

        // 保存后的回调
        $form->saved(function (Form $form) {
            admin_toastr('商品保存成功！');
        });

        return $form;
    }


}
