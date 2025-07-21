<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {
    }

    /**
     * История заказов пользователя
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $limit = $request->query('limit', 10);
            $userUuid = $request->user()->uuid;

            $orders = $this->orderService->getOrdersByUserUuid($userUuid, $limit);

            return response()->json([
                'data' => $orders->items(),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'total' => $orders->total(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Ошибка при получении заказов',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Создание заказа
     *
     * @param OrderRequest $request
     * @return JsonResponse
     */
    public function store(OrderRequest $request): JsonResponse
    {
        try {
            $userUuid = $request->user()->uuid;
            $products = $request->input('products', []);
            $comment = $request->input('comment');

            $order = $this->orderService->createOrder($userUuid, $products, $comment);

            return response()->json([
                'uuid' => $order->uuid,
                'status' => $order->status->value
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Ошибка при создании заказа',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
