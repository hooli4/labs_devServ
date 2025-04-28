<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;
use Jenssegers\Agent\Agent;
use App\Models\TwoFactorAuth;
use Carbon\Carbon;
use App\Mail\ConfirmCode;
use Illuminate\Support\Facades\Mail;

class TwoFactorAuthController extends Controller
{
    public function toggle(Request $request) {

        $request->validate(['password' => 'required|string']);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return response()->json(['message' => 'Incorrect password'], 401);
        }

        $user = Auth::user();

        $TwoFA_enabled_or_disabled = $user->Twofa;

        switch ($TwoFA_enabled_or_disabled) {
            case 0:
                $user->Twofa = 1;
                $user->save();
                return response()->json(['message' => 'Двухфакторная аутентификация включена']);
            case 1:
                $agent = new Agent();

                $ip = $request->ip();
                $browser = $agent->browser();
                $platform = $agent->platform();

                $user2FA = TwoFactorAuth::where('ip', $ip)->
                where('browser', $browser)->
                where('platform', $platform)->
                where('user_id', $user->id)->first();

                if ($user2FA->code != $request->code) {
                    return response()->json(['message' => 'Неправильный код'], 403);
                }

                if ($user2FA->expires_at < Carbon::now()) {
                    return response()->json(['message' => 'Код истек'], 403);
                }

                $user->Twofa = 0;
                $user->save();
                
                return response()->json(['message' => 'Двухфакторная аутентификация выключена']);
        }

    }

    public function requestCode(Request $request) {
        $user = User::find(Cookie::get('user_id'));

        if ($user->Twofa != 1) {
            return response()->json(['message' => 'TwoFactorAuth is off']);
        }

        $request->validate(['token2FA' => 'required|string']);

        $agent = new Agent();

        $ip = $request->ip();
        $browser = $agent->browser();
        $platform = $agent->platform();

        $user2FA = TwoFactorAuth::where('ip', $ip)->
        where('browser', $browser)->
        where('platform', $platform)->
        where('user_id', $user->id)->first();

        if (!$user2FA) {
            return response()->json(['message' => 'User is not found'], 404);
        }

        if (Hash::check($request->token2FA, $user2FA->token)) {

            $expire_at = (int) env('CODE_EXPIRE_AT');
            $limitation = 0;

            if ($user2FA->request_count >= 3) {
                $limitation = 30;
            }

            if (TwoFactorAuth::where('user_id', $user->id)->sum('request_count') >= 5) {
                $limitation = 50;
            }

            $now = Carbon::now();
            $end_of_limitation = Carbon::parse($user2FA->updated_at)->addSeconds($limitation);

            if ($now < $end_of_limitation) {
                $diff = $now->diff($end_of_limitation)->s;
                return response()->json(['message' => "Подождите $diff секунд"], 403);
            };

            $new_code = rand(100000, 999999);
            $user2FA->expires_at = Carbon::now()->addSeconds($expire_at);
            $user2FA->code = $new_code;
            $user2FA->request_count += 1;
            $user2FA->save();


            Mail::to($user->email)->send(new ConfirmCode($new_code));

            return response()->json(['message' => 'Код был запрошен']);
        }

        return response()->json([
            'message' => "Доступ запрещен",
         ], 403);

    }

    public function confirmCode(Request $request) {
        $user = User::find(Cookie::get('user_id'));

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user->Twofa != 1) {
            return response()->json(['message' => 'TwoFactorAuth is off']);
        }

        $request->validate(['token2FA' => 'required|string', 'code' => 'required']);

        $agent = new Agent();

        $ip = $request->ip();
        $browser = $agent->browser();
        $platform = $agent->platform();

        $user2FA = TwoFactorAuth::where('ip', $ip)->
        where('browser', $browser)->
        where('platform', $platform)->
        where('user_id', $user->id)->first();
        
        if (!$user2FA) {
            return response()->json(['message' => 'User is not found'], 404);
        }

        if (Hash::check($request->token2FA, $user2FA->token)) {
            if ($request->code != $user2FA->code) {
                return response()->json([
                    'message' => 'Неправильный код',
                ], 403);
            }

            if ($user2FA->expires_at < Carbon::now()) {
                return response()->json([
                    'message' => 'Код истек',
                ], 403);
            }

            $user2FA->code = null;
            $user2FA->expires_at = null;
            $user2FA->request_count = 0;
            $user2FA->save();

            $token = $user->createToken('API Token')->plainTextToken;

            $minutes = 5 * 365 * 24 * 60;
            $cookie = Cookie::make('remember', true, $minutes);

            return response()->json([
                'status' => 'success',
                'token' => $token,
                'message' => 'Вы успешно авторизовались',
            ])->cookie($cookie);
        }

        return response()->json([
           'message' => "Доступ запрещен",
        ], 403);
    }
}
