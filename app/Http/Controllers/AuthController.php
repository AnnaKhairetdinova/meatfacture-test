<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Exception;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {
    }

    /**
     * Регистрация пользователя
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $userData = $request->validated();

            $result = $this->authService->registerUser($userData);

            return response()->json([
                'token' => $result['token']
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Ошибка при регистрации',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Авторизация пользователя
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $phone = $validated['phone'];
            $password = $validated['password'];

            $result = $this->authService->loginUser($phone, $password);

            return response()->json([
                'access_token' => $result['token'],
                'expires_in' => auth('api')->factory()->getTTL() * 60
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Ошибка авторизации',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Выход из системы
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        try {
            $this->authService->logoutUser();
            return response()->json();
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Ошибка',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
