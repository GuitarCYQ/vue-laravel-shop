<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use App\Http\Resources\ProductSkusResource;

class ProductsResource extends JsonResource
{
    /**
     * 将资源转换为数组。
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            // 基础信息
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'image' => $this->image_url,
            'images' => $this->images ? json_decode($this->images, true) : [], // 解析JSON为数组

            // 销售状态
            'on_sale' => (bool)$this->on_sale,
            'status' => $this->status,
            'status_text' => $this->getStatusText(), // 状态文本描述

            // 价格与折扣
            'price' => (float)$this->price,
            'final_price' => (float)$this->final_price,
            'discount' => $this->discount ? (float)$this->discount : null,
            'discount_start_time' => $this->discount_start_time ? $this->discount_start_time->toDateTimeString() : null,
            'discount_end_time' => $this->discount_end_time ? $this->discount_end_time->toDateTimeString() : null,
            'is_discount_active' => $this->isDiscountActive(), // 是否正在折扣中

            // 统计信息
            'rating' => (float)$this->rating,
            'sold_count' => $this->sold_count,
            'review_count' => $this->review_count,
            'total' => $this->total_stock,

            // 分类信息
            'category_id' => $this->category_id,
            'category_name' => $this->whenLoaded('category', function () {
                return $this->category->name; // 关联分类名称（按需加载）
            }),

            // 标签状态
            'is_hot' => (bool)$this->is_hot,
            'is_new' => (bool)$this->is_new,

            // 时间信息
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,

            // sku 方法一
            // 'skus' => $this->whenLoaded('skus', function () {
            //     return $this->skus->map(function ($sku) {
            //         return [
            //             'id' => $sku->id,
            //             'title' => $sku->title,
            //             'price' => (float)$sku->price,
            //             'stock' => $sku->stock,
            //             'attributes' => $sku->attributes ? json_decode($sku->attributes, true) : [],
            //             'is_default' => (bool)$sku->is_default
            //         ];
            //     });
            // }),

            // sku方法二
            'skus' => ProductSkusResource::collection($this->whenloaded('skus')),

        ];
    }

    /**
     * 判断折扣是否有效（避免除以零）
     */
    protected function isValidDiscount()
    {
        // 折扣必须存在、大于0且小于等于1（1代表100%，即不打折）
        return isset($this->discount) && $this->discount > 0 && $this->discount <= 1;
    }

    /**
     * 获取状态文本描述
     */
    protected function getStatusText()
    {
        $statusMap = [
            1 => '正常',
            2 => '下架',
            3 => '预售'
        ];
        return $statusMap[$this->status] ?? '未知';
    }

    /**
     * 判断是否处于折扣有效期
     */
    protected function isDiscountActive()
    {
        if (!$this->discount || !$this->discount_start_time || !$this->discount_end_time) {
            return false;
        }

        $now = Carbon::now();
        return $now->between($this->discount_start_time, $this->discount_end_time);
    }
}
