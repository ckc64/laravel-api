<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {

        $data = json_decode($request->getContent(), true); //ensuring only the body is processed

        $validated = Validator::make($data ?? [], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validated->fails()) {
            return response()->json($validated->errors(), 403);
        }

        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $token = $user->createToken($data['name'] . 'Auth-Token')->plainTextToken;

            return response()->json([
                'message' => 'Registration Successful',
                'token_type' => 'Bearer',
                'access_token' => $token,
                'user' => $user,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function login(Request $request): JsonResponse
    {

        $data = json_decode($request->getContent(), true); //ensuring only the body is processed


        $validated = Validator::make($data ?? [], [
            'email' => 'required|email|string',
            'password' => 'required|string|min:6',
        ]);

        if ($validated->fails()) {
            return response()->json($validated->errors(), 403);
        }

        $credentials = ['email' => $data['email'], 'password' => $data['password']];

        try {
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'error' => 'Invalid Credentials'
                ], 403);
            }

            $user = User::where('email', $data['email'])->firstOrFail();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login Successful',
                'token_type' => 'Bearer',
                'access_token' => $token,
                'user' => $user,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'User has been logged out successfully'
        ], 200);
    }
}
