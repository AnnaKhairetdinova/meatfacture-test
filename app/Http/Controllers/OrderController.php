<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Random\RandomException;

class OrderController extends Controller
{
    /**
     * История заказов покупателя
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $limit = $request->query('limit', 10);
        $userUuid = $request->user()->uuid;
        $orders = Order::where('user_uuid', $userUuid)->paginate($limit);

        return response()->json([
            'data' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'pages' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Создание заказа
     *
     * @param OrderRequest $request
     * @return JsonResponse
     * @throws RandomException
     */
    public function store(OrderRequest $request): JsonResponse
    {
        $userUuid = $request->user()->uuid;
        $comment = $request->input('comment');
        $products = $request->input('products', []);
        $orderAmount = 0;

        foreach ($products as $item) {
            $product = Product::findOrFail($item['uuid']);
            $orderAmount += $product->price * $item['quantity'];
        }

        $order = Order::create([
            'user_uuid' => $userUuid,
            'comment' => $comment,
            'status' => OrderStatus::New,
            'order_amount' => $orderAmount,
        ]);

        foreach ($products as $item) {
            $product = Product::findOrFail($item['uuid']);

            $order->orderItems()->create([
                'product_uuid' => $product->uuid,
                'quantity' => $item['quantity'],
                'price_at_order' => $product->price,
            ]);
        }

        return response()->json(['uuid' => $order->uuid], 201);
    }
}
