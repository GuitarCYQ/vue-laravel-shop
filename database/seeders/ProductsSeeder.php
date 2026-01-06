<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\ProductCategory;


class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 正确代码
        $products = collect(); // 使用集合更方便处理批量数据

        // 批量创建商品并合并到集合中
        $products = $products->concat(Product::factory()->count(20)->create());
        $products = $products->concat(Product::factory()->hot()->count(10)->create());
        $products = $products->concat(Product::factory()->asNew()->count(10)->create());
        $products = $products->concat(Product::factory()->discount()->count(10)->create());

        // 遍历所有商品，为每个商品生成SKU
        foreach ($products as $product) {
            // 为单个商品生成3个SKU
            $skus = ProductSku::factory()
                ->count(3)
                ->create(['product_id' => $product->id]);

            // 更新商品价格为最低SKU价格
            $product->update(['price' => $skus->min('price')]);
        }
    }
}
