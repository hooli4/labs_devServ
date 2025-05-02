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
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;
use App\Models\TwoFactorAuth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;


class AuthController extends Controller
{

    public function test(Request $request) {
        $agent = new Agent();

        $browser = $agent->browser();
        $platform = $agent->platform();
        $ip = $request->ip();

        return response()->json(['browser' => $browser, 'ip' => $ip, 'platform' => $platform]);
    }
    public function UserRegister(RegisterRequest $request) {

        DB::transaction(function () use ($request) {
            try {
                User::create($request->all());
            }
            catch (\Exception $e) {
                throw $e;
            }

            $user = User::where("email", $request->email)->first();
            return response()->json(new UserResource($user), 201);
        });
    }

    public function UserLogin(LoginRequest $request) {

        if (Auth::attempt($request->only(['name', 'password']))) {

            $user = Auth::user(); 

            $agent = new Agent();

            $ip = $request->ip();
            $browser = $agent->browser();
            $platform = $agent->platform();

            $user2FA = TwoFactorAuth::where('ip', $ip)->
            where('browser', $browser)->
            where('platform', $platform)->
            where('user_id', $user->id)->first();

            $token2FA = Str::random(60);

            if ($user2FA) {
                $user2FA->token = $token2FA;
                $user2FA->save();
            }

            else {
                TwoFactorAuth::create([
                    'user_id' => $user->id,
                    'ip' => $ip,
                    'browser' => $browser,
                    'platform' => $platform,
                    'token' => $token2FA,
                ]);
            }

            $minutes = 60 * 24 * 365 * 5;
            $cookie = Cookie::make('user_id', $user->id, $minutes);

            if ($user->Twofa) {

                return response()->json([
                    'status' => 'success',
                    'token2FA' => $token2FA,
                    'message' => 'Выдан токен для аутентификации',
                ])->cookie($cookie);
            }

            else {

                $token = Auth::user()->createToken('API Token')->plainTextToken;
                $cookie2 = Cookie::make('remember', true, $minutes);

                return response()->json([
                    'status' => 'success',
                    'token' => $token,
                    'token2FA' => $token2FA,
                    'message' => 'Вы успешно авторизовались',
                ])->cookie($cookie)->cookie($cookie2);
            }
        }

        return response()->json(['error' => 'Wrong name or password'], 401);
    }

    public function userInfo() {
        if (!in_array('SUI', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "show-user-info"'],403);
        }
        return response()->json(new UserResource(Auth::user()));
    }

    public function Logout() {
        Auth::user()->currentAccessToken()->delete();
        $cookie = Cookie::forget('user_id');
        $cookie2 = Cookie::forget('remember');

        return response()->json(['message' => 'Успешное разлогирование'])->cookie($cookie)->cookie($cookie2);
    }

    public function deleteTokens() {
        Auth::user()->tokens()->delete();
        $cookie = Cookie::forget('user_id');
        $cookie2 = Cookie::forget('remember');
        return response()->json(['message' => 'Успешное разлогирование'])->cookie($cookie)->cookie($cookie2);
    }

    public function getTokens() {
        return response()->json(Auth::user()->tokens()->get());
    }

    public function changePassword(ChangePasswordRequest $request) {

        if (!in_array('ChangePass', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "change-password"'],403);
        }

        $user = Auth::user();
        
        DB::transaction(function () use ($user, $request) {
            try {
                if (Hash::check($request->password, $user->password)) {
                    $user->password = Hash::make($request->new_password);
                    $user->save();
        
                    return response()->json(['message' => 'Пароль был успешно изменён']);
                }

                return response()->json(['message' => 'Введён неправильный пароль'], 403);
            }
            catch (\Exception $e) {
                throw $e;
            }
        });
    }
}
