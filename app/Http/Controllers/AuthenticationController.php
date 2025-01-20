<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateAccountRequest;
use App\Http\Requests\LoginAccountRequest;
use App\Models\User;
use App\Traits\AuthenticationTrait;
use App\Traits\ResponseTrait;

class AuthenticationController extends Controller
{
    use AuthenticationTrait, ResponseTrait;

    public function create(CreateAccountRequest $request): \Illuminate\Http\JsonResponse
    {
        $validatedData = $request->validated();

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
        ]);

        $token = $this->createAuthenticationToken($user);

        return $this->successResponse(['user' => $validatedData, 'token' => $token], 'User created successfully.');
    }

    public function login(LoginAccountRequest $request): \Illuminate\Http\JsonResponse
    {
        $validatedData = $request->validated();

        $user = User::where('email', $validatedData['email'])->first();

        if (!$user || !password_verify($validatedData['password'], $user->password)) {
            return $this->errorResponse('Invalid email or password.', 401);
        }

        $token = $this->createAuthenticationToken($user);

        return $this->successResponse(['token' => $token, 'user' => $user->toArray()], 'User logged in successfully.');
    }
}
