<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\ProductsResource;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        // \DB::enableQueryLog();
        $query = Product::query()->onSale();

        // 搜索
        if ($search = $request->search) {
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($like) {
                $q->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhereHas('skus', function($q) use ($like) {
                        $q->where('title', 'like', $like)
                            ->orWhere('description', 'like', $like);
                    });
            });
        }

        switch ($request->type) {
            case 'hot' :
                $query->hot();
                break;
            case 'discount' :
                $query->discount();
                break;
            case 'new':
                $query->new();
                break;
        }

        // \Log::info('category_id:',$request->category_id);
        // 分类筛选
        if (!empty($request->category_id)&& empty($request->subcategory_id)) {
            $query->where('category_id', $request->category_id);
        }

        if (!empty($request->category_id) && !empty($request->subcategory_id)) {
            $query->where('category_id', $request->subcategory_id);
        }

        // 价格区间筛选
        if ($request->has('min_price') && $request->min_price !== null) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price !== null) {
            $query->where('price', '<=', $request->max_price);
        }

        // \DB::enableQueryLog();


        // 排序处理
        // order 参数用来控制商品的排序规则
        if ($sort = $request->input('sort', 'id')) {
            $sortMap = [
                'default' => ['id', 'desc'],
                'price_asc' => ['price', 'asc'],
                'price_dasc' => ['price', 'desc'],
                'sales' => ['sold_count', 'desc'],
            ];
            if (isset($sortMap[$sort])) {
                list($column, $direction) = $sortMap[$sort];
                $query->orderBy($column, $direction);
            }
        }


        // 分页处理
        $limit = $request->limit
            ? min(max(intval($request->limit), 1), 50)
            : 16;
        $products = $query->paginate($limit);

        // $perPage = $request->input('per_page', '16');
        // $page = $request->input('page', '1');
        // $perPage = min(max(intval($perPage), 1), 50);
        // $page = max(intval($page), 1);
        // $products = $query->paginate($perPage, ['*'], 'page', $page);

        // \Log::info('sql:', \DB::getQueryLog());

        return ProductsResource::collection($products);
    }

    public function show(Product $product)
    {
        // // 判断商品是否已经上架，如果没有上架则抛出异常
        if (!$product->on_sale) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => '商品未上架'
                ],403)
            );
        }

        // ProductsResource里用了skus模型，所以预加载
        $product->load('skus');

        return new ProductsResource($product);
    }
}
