<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cookie;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function UserRegister(RegisterRequest $request) {

        $user = User::create($request->all());

        return response()->json(new UserResource($user), 201);

    }

    public function UserLogin(LoginRequest $request) {

        if (Auth::attempt($request->only(['name', 'password']))) {

            $token = Auth::user()->createToken('API Token')->plainTextToken;

            Cookie::make('remember_auth', 'true', 30);

            return response()->json(['token' => $token], 200);
        }

        return response()->json(['error' => 'Wrong name or password'], 401);
    }

    public function userInfo() {
        return response()->json(new UserResource(Auth::user()));
    }

    public function Logout() {
        Auth::user()->currentAccessToken()->delete();
        Cookie::forget('remember_auth');
    }

    public function deleteTokens() {
        Auth::user()->tokens()->delete();
    }

    public function getTokens() {
        return response()->json(Auth::user()->tokens()->get());
    }

    public function changePassword(ChangePasswordRequest $request) {
        $user = Auth::user();

        if (Hash::check($request->password, $user->password)) {
            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json(['message' => 'Пароль был успешно изменён']);
        }

        return response()->json(['message' => 'Введён неправильный пароль'], 403);
    }

}
