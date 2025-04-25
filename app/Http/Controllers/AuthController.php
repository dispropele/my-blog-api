<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * Register new user
     * POST /api/auth/register
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
           'avatar' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
           'login' => 'required|string|unique:users,login',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|confirmed',
        ]);

        $avatar = $request->file('avatar');

        if($request->hasFile('avatar') && $avatar->isValid()){
            $avatarPath = $avatar->store('avatars', 'public');
            $validated['avatar'] = $avatarPath;
        } else{
            unset($validated['avatar']);
        }
        unset($validated['password_confirmation']);

        $user = User::create($validated);
        $user->assignRole('user');

        return response()->json(new UserResource($user), 201);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * Login user
     * POST /api/auth/login
     */
    public function login(Request $request){
        $validated = $request->validate([
           'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if(!Auth::attempt($validated)){
            return response()->json([
                'success' => false,
                'message' => 'Invalid login details'
            ], 401);
        }

        $user = Auth::user();
        //Удаляем существующие токены
        $user->tokens()->delete();
        //Создаем новый токен
        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user->load('roles')),
            //Возвращаем пользователя с его ролью
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     * Logout this user
     * POST /api/auth/logout
     */
    public function logout(Request $request){
        //Удаляем текущий токен
        $request->user()->currentAccessToken()->delete();
        return response()->noContent();
    }

    /**
     * @param Request $request
     * @return UserResource
     *
     * Get the authenticated User.
     * GET /api/auth/user
     */
    public function user(Request $request){
        return new UserResource($request->user()) ;
    }

}
