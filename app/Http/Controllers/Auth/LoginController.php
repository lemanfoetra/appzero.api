<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class LoginController extends Controller
{

    private $expiredToken;

    public function __construct()
    {
        $this->expiredToken = Carbon::now()->addDay(3);
    }


    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user   = Auth::user();
        $token  = $user->createToken('auth_token')
            ->plainTextToken;

        $idToken = explode('|', $token)[0] ?? null;
        DB::table('personal_access_tokens')
            ->where('id', $idToken)
            ->update(['expires_at'  => $this->expiredToken]);

        return response()->json([
            'user'  => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expired_token' => date('Y-m-d H:i:s', strtotime($this->expiredToken)),
        ]);
    }


    public function refreshToken(Request $request)
    {
        $user       = $request->user();
        $oldToken   = PersonalAccessToken::findToken($request->bearerToken());

        if ($oldToken) {
            // Hapus token lama
            $oldToken->delete();

            // Buat token baru
            $newToken       = $user->createToken('auth_token')->plainTextToken;
            $idToken = explode('|', $newToken)[0] ?? null;
            DB::table('personal_access_tokens')
                ->where('id', $idToken)
                ->update(['expires_at'  => $this->expiredToken]);

            return response()->json([
                'user'          => $user,
                'access_token' => $newToken,
                'token_type' => 'Bearer',
                'expired_token' => date('Y-m-d H:i:s', strtotime($this->expiredToken)),
            ]);
        }

        return response()->json(['message' => 'Invalid token'], 401);
    }
}
