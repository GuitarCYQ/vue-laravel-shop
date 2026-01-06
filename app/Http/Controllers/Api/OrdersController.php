<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\Api\OrderRequest;
use App\Models\ProductSku;
use App\Models\Order;
use App\Models\UserAddress;
use Carbon\Carbon;
use App\Jobs\CloseOrder;
use App\Http\Resources\OrderResource;
use App\Services\CartService;
use App\Services\OrderService;
use App\Exceptions\InvalidRequestException;
use App\Http\Requests\SendReviewRequest;


class OrdersController extends Controller
{
    // 订单列表
    public function index(Request $request)
    {
        $orders = Order::query()
        // 使用 with 方法预加载，避免 N+1 问题
        ->with(['items.product', 'items.productSku'])
        ->where('user_id', $request->user()->id)
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        return OrderResource::collection($orders);
    }

    // 订单详情
    public function show(Order $order, Request $request) {
        $this->authorize('own', $order);
        // 预加载管理啊数据（商品、sku谢谢）
        $order->load(['items.productSku', 'items.product']);

        // 使用资源类格式输出
        return new OrderResource($order);
    }

    // 创建订单
    public function store(OrderRequest $request, OrderService $orderService)
    {
        $user    = $request->user();
        $address = UserAddress::find($request->input('address_id'));

        return $order = $orderService->store($user, $address, $request->input('remark'), $request->input('items'));

    }

    // 确认收货
    public function received(Order $order)
    {
        // 校验权限
        $this->authorize('own', $order);

        // 判断订单的发货状态是否为已发货
        if ($order->ship_status === Order::SHIP_STATUS_PENDING) {
            throw new InvalidRequestException('发货状态不正确');
        }

        // 更新发货状态为已收到
        $order->update([
            'ship_status' => Order::SHIP_STATUS_RECEIVED
        ]);

        return response()->json([
            'success' => true,
            'message' => '确认收货成功'
        ], 201);
    }

    // 查看评价
    public function review(Order $order) {
        // 校验权限
        $this->authorize('own', $order);
        // 2. 校验订单状态（必须已支付）
        if (!$order->paid_at) {
            return response()->json([
                'success' => false,
                'message' => '该订单未支付，不可评价'
            ], 400); // 400 Bad Request
        }

        // 订单是否已评价
        if ($order->reviewed) {
            return response()->json([
                'success' => false,
                'message' => '该订单已评价，不可重复提交'
            ], 400);
        }

        // 使用load方法加载管理数据 避免 N+1 问题
        $order->load([
            'tiems.productSku' => function ($query) {
                $query->select('id', 'product_id', 'title', 'image'); // 只返回前端需要的字段
            },
            'items.product' => function ($query) {
                $query->select('id', 'name'); // 只返回前端需要的字段
            },
        ]);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    // 评论
    public function sendReview(Order $order, SendReviewRequest $request) {
        // 校验权限
        $this->authorize('own', $order);

        // 状态检验
        if (!$order->paid_at) {
            return response()->json([
                'success' => false,
                'message' => '该订单未支付',
            ], 400);
        }
        if ($order->reviewed) {
            return response()->json([
                'success' => false,
                'message' => '该订单已评价',
            ], 400);
        }

        // 处理评价数据
        $reviews = $request->input('reviews');
        \DB::transaction(function() use ($reviews, $order) {
            // 遍历以后提交的数据
            foreach ($reviews as $review) {
                $orderItem = $order->items()->find($review['id']);
                // 保存频分和评价
                $orderItem->update([
                    'rating' => $review['rating'],
                    'review' => $review['review'],
                    'reviewed_at' => Carbon::now(),
                ]);
            }
            // 将订单标记为已评价
            $order->update(['reviewed' => true]);
        });
        return response()->json([
            'success' => true,
            'message' => '评价成功',
            'data' => $order,
        ], 201);
    }

    // 取消订单
    public function cancelOrder(Order $order) {
        // 校验权限
        $this->authorize('own', $order);
        // 判断订单状态是已支付
        if ($order->ship_status === Order::ORDER_STATUS_PAID) {
            throw new InvalidRequestException('订单状态不正确，订单已支付了');
        }
        
        // 取消订单
        $order->update([
            'closed' => 1,
            'refund_status' => Order::ORDER_STATUS_CANCELLED, // 状态修改成取消订单
        ]);

        return response()->json([
            'success' => true,
            'message' => '取消订单成功',
        ]);
    }
}
