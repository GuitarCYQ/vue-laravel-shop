<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ProductSku;
use App\Models\Product;
use Faker\Factory as FakerFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductSku>
 */
class ProductSkuFactory extends Factory
{
    protected $model = ProductSku::class;

    public function definition()
    {
        // 创建中文Faker实例（不依赖Text类）
        $faker = FakerFactory::create('zh_CN');

        // SKU属性组合
        $colors = ['红色', '黑色', '白色', '银色', '深空灰', '金色', '蓝色', '绿色'];
        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '均码'];
        $capacities = ['32G', '64G', '128G', '256G', '512G'];

        $attributes = [];
        if (rand(0, 1)) $attributes[] = $faker->randomElement($colors);
        if (rand(0, 1)) $attributes[] = $faker->randomElement($sizes);
        if (rand(0, 1)) $attributes[] = $faker->randomElement($capacities);
        if (empty($attributes)) $attributes[] = $faker->randomElement($colors);

        $title = implode('-', $attributes);

        // 扩展中文句子库，确保有足够的中文描述
        $chineseSentences = [
            '这款商品质量优良，值得购买。',
            '精选材料制作，耐用性强。',
            '多种规格可选，满足不同需求。',
            '经过严格质检，品质有保障。',
            '设计人性化，使用便捷。',
            '性价比高，适合大众消费。',
            '包装精美，适合送礼。',
            '新款上市，限时优惠。',
            '库存充足，下单即发。',
            '售后完善，七天无理由退换。'
        ];

        return [
            'title' => $title,
            // 直接使用自定义中文句子库，避免依赖不存在的类
            'description' => $chineseSentences[array_rand($chineseSentences)],
            'price' => $faker->randomFloat(2, 50, 3000),
            'stock' => $faker->numberBetween(10, 1000),
        ];
    }
}
