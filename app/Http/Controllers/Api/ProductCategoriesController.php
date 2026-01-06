<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Http\Resources\ProductCategoriesResource;

class ProductCategoriesController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductCategory::query();

        // 筛选有图标的分类（不影响子分类查询，仅过滤符合条件的分类）
        if ($request->type == 'icon') {
            $query->whereNotNull('icon');
        }

        // 1. 查询所有符合条件的分类（包括父分类和子分类）
        $allCategories = $query->get();

        // 调用修复后的树形构建方法
        $categories = $this->buildTree($allCategories);

        // 3. 返回树形结构（转换为集合以便Resource处理）
        return ProductCategoriesResource::collection(collect($categories));
    }

    /**
     * 递归构建树形结构
     * @param $categories 所有分类集合
     * @param int $parentId 父分类ID（顶级分类为0，根据数据库实际值修改）
     * @return array
     */
    public function buildTree($categories)
    {
        // 1. 先将分类转为以ID为键的数组，方便查找
        $categoryMap = [];
        foreach ($categories as $category) {
            // 使用模型设置器初始化children
            $category->setChildrenAttribute([]);
            $categoryMap[$category->id] = $category;
        }

        $tree = [];
        foreach ($categories as $category) {
            $parentId = $category->parent_id;

            // 2. 顶级分类：parent_id为null、0、'' 或不存在于分类列表中（防止孤立分类）
            if (empty($parentId) || !isset($categoryMap[$parentId])) {
                $tree[] = $category;
            } else {
                // 通过设置器修改父分类的children属性
                $parent = $categoryMap[$parentId];
                $children = $parent->children;
                $children[] = $category;
                $parent->setChildrenAttribute($children);
            }
        }

        return $tree;
    }
}
?>
