<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\AddCartRequest;
use App\Models\ProductSku;
use App\Models\CartItem;
use App\Http\Resources\CartResource;
use App\Services\CartService;


class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index(Request $request)
    {
        $cartItems = $this->cartService->get();
        return CartResource::collection($cartItems);
    }

    public function store(AddCartRequest $request)
    {

        $cart = $this->cartService->add($request->input('sku_id'),$request->input('amount'));

        return new CartResource($cart);
    }

    public function destroy(Request $request) {
        $ids = $request->input('ids');

        // 验证参数（确保ids存在且为数组）
        if (empty($ids) || !is_array($ids)) {
            return response()->json([
                'message' => '请提供有效的商品ID'
            ], 400);
        }

        $this->cartService->remove($ids);


        return response()->json([
            'status' => 204,
            'message' => '删除成功'
        ],204);
    }
}
