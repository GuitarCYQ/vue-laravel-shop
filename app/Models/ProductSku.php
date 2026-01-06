<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSku extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'description', 'price', 'stock'];

    // 所属的商品
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // 减少库存
    public function decreaseStock($amount) {
        if ($amount < 0) {
            throw new InternalException('减库存不可小于0');
        }

        return $this->where('id', $this->id)->where('stock', '>=', $amount)->decrement('stock', $amount);
    }

    // 增加库存
    public function addStock($amount) {
        if ($amount < 0) {
            throw new InternalException('加库存不可小于0');
        }
        $this->increment('stock', $amount);
    }
}
