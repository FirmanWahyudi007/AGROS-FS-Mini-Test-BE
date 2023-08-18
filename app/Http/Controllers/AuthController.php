<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\CreateUserRequest;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(CreateUserRequest $request)
    {
        $data = $request->validated();
        unset($data['password_confirmation']);
        $data['password'] = bcrypt($data['password']);

        $user = User::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'data' => $user->only(['name', 'email', 'city', 'role']),
        ], Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!$token = auth()->attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], Response::HTTP_UNAUTHORIZED);
        }

        //kirim data user dan token ke client sebagai response dari request yang berhasil

        return response()->json([
            'status' => 'success',
            'message' => 'Login success',
            'data' => [
                'user' => auth()->user()->only(['name', 'email', 'city', 'role']),
                'token' => $this->respondWithToken($token),
            ],
        ], Response::HTTP_OK);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Refresh token success',
            'data' => [
                'token' => $this->respondWithToken(auth()->refresh()),
            ],
        ], Response::HTTP_OK);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout success',
        ], Response::HTTP_OK);
    }

    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
        ];
    }
}
