<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;


class UserController extends Controller
{
    public function index()
    {
        $users = User::all();

        return response()->json([
            'status' => 'success',
            'message' => 'users retrieved successfully',
            'data' => $users,
        ], Response::HTTP_OK);
    }

    public function profile()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Profile retrieved successfully',
            'data' => auth()->user()->only(['name', 'email', 'city', 'role']),
        ], Response::HTTP_OK);
    }

    //update profile
    public function updateProfile(ProfileUpdateRequest $request)
    {
        $request->user()->fill($request->validated())->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'data' => auth()->user()->only(['name', 'email', 'city', 'role']),
        ], Response::HTTP_OK);
    }

    public function create(CreateUserRequest $request)
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

    //update user
    public function update(CreateUserRequest $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $data = $request->validated();
        unset($data['password_confirmation']);
        $data['password'] = bcrypt($data['password']);

        $user->fill($data)->save();

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => $user->only(['name', 'email', 'city', 'role']),
        ], Response::HTTP_OK);
    }

    public function delete($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully',
        ], Response::HTTP_OK);
    }
}
