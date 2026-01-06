<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory,SoftDeletes,DefaultDatetimeFormat;
    protected $fillable =  [
        'title',
        'description',
        'image',
        'images',
        'on_sale',
        'rating',
        'sold_count',
        'review_count',
        'price',
        'category_id',
        'status',
        'slug',
        'is_hot',
        'discount_start_time',
        'discount_end_time',
        'discount',
        'is_new',
    ];

    // 自动转换为Carbon对象
    protected $dates = [
        'discount_start_time',
        'discount_end_time',
    ];

    // 所属分类
    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    // 商品的SKU
    public function skus()
    {
        return $this->hasMany(ProductSku::class);
    }

     // 生成完整的图片路径，（如果是网络地址就不需要）
     public function getImageUrlAttribute()
     {
         // 如果 image 字段本身就已经是完整的url 就直接返回
         if (Str::startsWith($this->attributes['image'], ['http://', 'https://'])) {
             return $this->attributes['image'];
         }

         return \Storage::disk('public')->url($this->attributes['image']);
     }



    // 最终价格（经过折扣）
    public function getFinalPriceAttribute()
    {
        // 折扣有效时：最终价格 = 原价 * 折扣比例
        if ($this->isDiscountValid()) {
            $finalPrice = $this->price * $this->discount;
        }
        // 折扣无效时：最终价格 = 原价
        else {
            $finalPrice = $this->price;
        }

        // 四舍五入保留2位小数
        $finalPrice = round($finalPrice, 2);

        return $finalPrice;
    }


    // 折扣是否触发
    public function isDiscountValid()
    {
        // 1. 必须设置了折扣比例且小于1（即打折）
        if (empty($this->discount) || $this->discount >= 1.00) {
            return false;
        }

        // 2. 必须同时设置了开始和结束时间
        if (empty($this->discount_start_time) || empty($this->discount_end_time)) {
            return false;
        }

        // 3. 当前时间必须在折扣时间范围内
        $now = Carbon::now();
        $start = Carbon::parse($this->discount_start_time);
        $end = Carbon::parse($this->discount_end_time);

        $valid = $now->between($start, $end);

        return $valid;
    }


    // 获取商品的总库存
    public function getTotalStockAttribute()
    {
        return $this->skus->sum('stock');
    }

    // 热门商品查询
    public function scopeHot(Builder $query)
    {
        return $query->where('is_hot', true);
    }

    // 折扣商品查询
    public function scopeDiscount(Builder $query)
    {
        return $query->whereNotNull('discount')
            ->where('discount', '<', 1.00)
            ->whereNotNull('discount_start_time')
            ->whereNotNull('discount_end_time')
            ->where('discount_start_time', '<=', now())
            ->where('discount_end_time', '>=', now());
    }

    // 新品上市查询（手动改标记或创建30天以内的商品）
    public function scopeNew(Builder $query, $days = 30)
    {
        // 优先使用手动标记，若无则按创建时间判断
        return $query->where(function($q) use ($days) {
            $q->where('is_new', 1)

                ->orWhere('created_at', '>=', Carbon::now()->subDays($days));
        });
    }

    /**
     * 在售商品
     */
    public function scopeOnSale(Builder $query)
    {
        return $query->where('on_sale', true);
    }
}
