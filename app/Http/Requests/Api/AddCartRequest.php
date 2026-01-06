<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ProductSku;

class AddCartRequest extends FormRequest
{

    // 先知晓基础规则验证，在执行自定义规则
    protected $stopOnFirstFailure = false; // 默认即可，缺

    public function rules()
    {
        return [
            'sku_id' => [
                'required',
                'exists:product_skus,id', // 验证sku_id是否存在
                function  ($attribute, $value, $fail) {
                    // 预加载product关联，避免额外查询
                    $sku = ProductSku::with('product')->find($value);
                    if (!$sku->product->on_sale) {
                        return $fail('该商品未上架');
                    }
                    if ($sku->stock === 0) {
                        return $fail('该商品已售完');
                    }
                    if ($this->input('amount') > 0 && $sku->stock < $this->input('amount')) {
                        return $fail('该商品库存不足');
                    }
                },
            ],
            'amount' => ['required', 'integer', 'min:1'],
        ];
    }

    public function attributes() {
        return [
            'amount' => '商品数量'
        ];
    }

    public function messages() {
        return [
            'sku_id.required' => '请选择商品'
        ];
    }

}
