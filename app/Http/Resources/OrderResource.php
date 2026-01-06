<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Order;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'no' => $this->no,
            'created_at' => $this->created_at->toDateTimeString(), // 标准化时间格式
            'status_text' => $this->getStatusText(), // 订单状态文本
            'ship_status' => $this->ship_status,
            'ship_status_text' => $this->getShipStatusText(), // 物流状态文本
            'refund_status' => $this->refund_status,
            'refund_status_text' => $this->getRefundStatusText(), //退款状态文本
            'refund_no' => $this->refund_no, //退款状态文本
            'total_amount' => $this->total_amount, // 单位：分
            'address' => $this->address, // 收货地址信息
            'items' => $this->getOrderItems(), // 订单商品列表
            'ship_data' => $this->ship_data, // 物流信息（快递单号等）
            'paid_at' => $this->paid_at?->toDateTimeString(), // 支付时间
            'payment_methods' => $this->payment_methods, // 支付时间
            'payment_no' => $this->payment_no, // 支付时间
            'closed'   => $this->closed, // 订单是否关闭
        ];
    }

    // 获取订单状态文本
    protected function getStatusText()
    {
        $statusMap = [
            Order::ORDER_STATUS_UNPAID => '待支付',
            Order::ORDER_STATUS_PAID => '已支付',
            Order::ORDER_STATUS_CANCELLED => '已取消',
        ];

        return $statusMap;
    }

    // 获取物流状态文本
    protected function getShipStatusText()
    {
        return Order::$shipStatusMap[$this->ship_status] ?? '未知物流状态';
    }

    // 获取退款状态文本
    protected function getRefundStatusText()
    {
        return Order::$refundStatusMap[$this->refund_status] ?? '未知退款状态';
    }

    // 格式化订单商品列表
    protected function getOrderItems()
    {
        return $this->items->map(function ($item) {
            return [
                'sku_id' => $item->product_sku_id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->title,
                'product_img' => $item->product->image,
                'sku_name' => $item->productSku->title,
                'price' => $item->price, // 总价
                'amount' => $item->amount,
            ];
        });
    }


}
