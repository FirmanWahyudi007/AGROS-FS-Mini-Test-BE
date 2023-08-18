<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();

        return response()->json([
            'status' => 'success',
            'message' => 'users retrieved successfully',
            'data' => $users,
        ], 200);
    }

    public function profile()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Profile retrieved successfully',
            'data' => auth()->user()->only(['name', 'email', 'city', 'role']),
        ], 200);
    }
}
