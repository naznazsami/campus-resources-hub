<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        // The 'hashed' cast on User hashes the password when it is saved.
        $user = User::create($request->validated());

        return $this->respondWithToken($user)->setStatusCode(201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        // Same message whether the email or the password is wrong,
        // so the response doesn't reveal which emails are registered.
        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        return $this->respondWithToken($user);
    }

    public function logout(Request $request): Response
    {
        // Revoke only the token used for this request; other devices stay logged in.
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    private function respondWithToken(User $user): JsonResponse
    {
        $token = $user->createToken('auth_token')->plainTextToken;

        return (new UserResource($user))
            ->additional(['token' => $token])
            ->response();
    }
}
