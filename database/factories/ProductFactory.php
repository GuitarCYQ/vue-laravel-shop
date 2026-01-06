<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ProductCategory;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Faker\Factory as FakerFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        $faker = FakerFactory::create('zh_CN');

        // 八大类别产品库及对应图片分类
        $productTypes = [
            // 服饰类
            '服饰' => [
                '产品' => ['T恤', '牛仔裤', '连衣裙', '夹克', '衬衫', '卫衣', '西装', '短裙'],
                '图片分类' => 'fashion'
            ],
            // 电子产品
            '电子产品' => [
                '产品' => ['智能手机', '笔记本电脑', '平板电脑', '智能手表', '蓝牙耳机', '游戏机', '相机', '充电宝'],
                '图片分类' => 'electronics'
            ],
            // 家居类
            '家居' => [
                '产品' => ['沙发', '餐桌', '椅子', '床', '衣柜', '台灯', '地毯', '窗帘'],
                '图片分类' => 'furniture'
            ],
            // 美妆类
            '美妆' => [
                '产品' => ['口红', '粉底液', '面膜', '香水', '眼影', '防晒霜', '卸妆水', '精华液'],
                '图片分类' => 'beauty'
            ],
            // 食品类
            '食品' => [
                '产品' => ['巧克力', '饼干', '坚果', '咖啡', '茶叶', '蜂蜜', '薯片', '牛肉干'],
                '图片分类' => 'food'
            ],
            // 运动类
            '运动' => [
                '产品' => ['运动鞋', '运动服', '瑜伽垫', '跑步机', '篮球', '羽毛球拍', '健身环', '运动背包'],
                '图片分类' => 'sports'
            ],
            // 母婴类
            '母婴' => [
                '产品' => ['婴儿奶粉', '纸尿裤', '婴儿车', '玩具', '童装', '奶瓶', '婴儿床', '安抚奶嘴'],
                '图片分类' => 'baby'
            ],
            // 图书类
            '图书' => [
                '产品' => ['小说', '教科书', '杂志', '儿童绘本', '工具书', '历史书', '科普书', '漫画'],
                '图片分类' => 'books'
            ]
        ];

        // 随机选择一个大类别
        $mainCategory = array_rand($productTypes);
        $categoryData = $productTypes[$mainCategory];

        // 从该类别中选择具体产品
        $productType = $faker->randomElement($categoryData['产品']);
        $imageCategory = $categoryData['图片分类'];

        // 品牌/系列前缀
        $prefixes = [
            '超级', '极致', '轻盈', '专业', '乐享',
            '炫彩', '智联', '风尚', '经典', '新锐',
            '天然', '有机', '环保', '智能', '舒适'
        ];

        // 型号/版本后缀
        $suffixes = [
            'Pro', 'Max', 'Mini', 'Ultra', 'Plus',
            '青春版', '旗舰版', '标准版', '尊享版',
            '2024新款', '限量版', '升级版', '精选款'
        ];

        // 随机组合生成产品名称
        $nameParts = [$productType];
        if (rand(0, 2) < 1) { // 30%概率添加前缀
            array_unshift($nameParts, $faker->randomElement($prefixes));
        }
        if (rand(0, 1) < 1) { // 50%概率添加后缀
            $nameParts[] = $faker->randomElement($suffixes);
        }
        $name = implode(' ', $nameParts);

        // 生成与产品类型匹配的可访问图片
        $imageId = rand(1, 100); // 固定范围内的图片ID，确保稳定可访问
        $mainImage = "https://picsum.photos/seed/{$imageCategory}{$imageId}/800/800";

        // 生成配套的轮播图（同一类别不同角度）
        $carouselImages = [];
        for ($i = 1; $i <= 3; $i++) {
            $carouselImages[] = "https://picsum.photos/seed/{$imageCategory}{$imageId}{$i}/1200/600";
        }

        $originalPrice = $faker->randomFloat(2,50,3000);
        $baseSlug = Str::slug($name);
        $uniqueSlug = $baseSlug . '-' . Str::random(5);

        // 按类别定制描述
        $categoryDescriptions = [
            '服饰' => ['面料舒适透气，版型修身显瘦', '时尚设计，百搭款式，适合多种场合', '精选优质面料，做工精细耐穿'],
            '电子产品' => ['高性能配置，运行流畅', '高清显示，色彩逼真', '续航持久，充电快速'],
            '家居' => ['环保材料，健康无异味', '简约设计，百搭家居风格', '稳固耐用，承重力强'],
            '美妆' => ['天然成分，温和不刺激', '质地轻盈，易吸收', '效果持久，不易脱妆'],
            '食品' => ['纯天然原料，无添加剂', '口感醇厚，回味无穷', '独立包装，方便携带'],
            '运动' => ['透气面料，运动不闷汗', '人体工学设计，运动更舒适', '耐磨材质，使用寿命长'],
            '母婴' => ['安全无毒，专为宝宝设计', '柔软亲肤，呵护宝宝娇嫩肌肤', '易清洗，耐磨损'],
            '图书' => ['纸张优质，印刷清晰', '内容丰富，可读性强', '装订牢固，不易掉页']
        ];

        return [
            'title'        => $name,
            'description'  => $faker->randomElement($categoryDescriptions[$mainCategory]),
            'category_id'  => ProductCategory::inRandomOrder()->first()->id ?? 1,
            'image'        => $mainImage,
            'images'       => json_encode($carouselImages),
            'price'        => $originalPrice,
            'on_sale'      => true,
            'rating'       => $faker->numberBetween(0, 5),
            'sold_count'   => $faker->numberBetween(0, 500),
            'review_count' => $faker->numberBetween(0, 200),
            'is_hot'       => false,
            'is_new'       => false,
            'discount'     => null,
            'discount_start_time' => null,
            'discount_end_time' => null,
            'slug'         => $uniqueSlug,
            'created_at'   => $faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    // 热门商品状态
    public function hot()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_hot' => true,
                'sold_count' => $this->faker->numberBetween(1000, 5000),
                'rating' => $this->faker->randomFloat(1, 4.5, 5.0),
            ];
        });
    }

    // 新品状态
    public function asNew() {
        return $this->state(function (array $attributes) {
            return [
                'is_new' => true,
                'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
                'sold_count' => $this->faker->numberBetween(0, 300),
            ];
        });
    }

    // 折扣商品状态
    public function discount()
    {
        $discountRate = $this->faker->randomFloat(2, 0.3, 0.9);
        $startTime = Carbon::now()->subDays(rand(1,3));
        $endTime = (clone $startTime)->addDays(rand(2,7));

        return $this->state(function (array $attributes) use ($discountRate, $startTime, $endTime) {
            return [
                'discount' => $discountRate,
                'discount_start_time' => $startTime,
                'discount_end_time' => $endTime,
                'price' => $this->faker->randomFloat(2, 200, 5000),
            ];
        });
    }
}
