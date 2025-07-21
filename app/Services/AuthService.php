<?php

namespace App\Services;

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Регистрация пользователя
     *
     * @param array $userData
     * @return array
     * @throws ValidationException
     */
    public function registerUser(array $userData): array
    {
        $existingUser = User::where('phone', $userData['phone'])->first();

        if ($existingUser) {
            throw ValidationException::withMessages([
                'phone' => 'Пользователь уже существует'
            ]);
        }

        $user = User::create([
            'phone' => $userData['phone'],
            'name' => $userData['name'],
            'address' => $userData['address'] ?? null,
            'email' => $userData['email'] ?? null,
            'password' => Hash::make($userData['password']),
        ]);

        $token = JWTAuth::fromUser($user);

        return [
            'token' => $token
        ];
    }

    /**
     * Авторизация пользователя
     *
     * @param string $phone
     * @param string $password
     * @return array
     * @throws ValidationException
     */
    public function loginUser(string $phone, string $password): array
    {
        $credentials = [
            'phone' => $phone,
            'password' => $password
        ];

        $token = auth('api')->attempt($credentials);

        if (!$token) {
            throw ValidationException::withMessages([
                'phone' => 'Неверный номер телефона или пароль'
            ]);
        }

        return [
            'token' => $token
        ];
    }

    /**
     * Выход из системы
     *
     * @return bool
     */
    public function logoutUser(): bool
    {
        auth('api')->logout();
        return true;
    }
}
