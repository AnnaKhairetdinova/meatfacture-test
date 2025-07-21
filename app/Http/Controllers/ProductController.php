<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Список товаров
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $limit = $request->query('limit', 10);

            $products = Product::with('category')->paginate($limit);

            $productData = $products->map(function ($product) {
                return [
                    'id' => $product->uuid,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'category' => $product->category->name,
                    'stock' => $product->stock,
                ];
            });

            return response()->json([
                'data' => $productData,
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'total' => $products->total(),
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Ошибка при получении товаров',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
