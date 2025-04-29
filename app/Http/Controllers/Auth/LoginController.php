<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        $username = $request->input('username');
        $password = $request->input('password');
        if($username === 'Daptee' && $password === 'Daptee2025')
        {
            $token = Str::random(60);
            cache()->put("token:$token", true, now()->addHour());
            return response()->json(['token' => $token]);
        }
        return response()->json(['message' => 'Credenciales Invalidas'], 401);
    }
}
