<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }

        try {
            $credentials = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
            $user = \App\Models\User::find($credentials->user_id);

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Set the authenticated user
            Auth::setUser($user);

        } catch (Exception $e) {
            return response()->json(['error' => 'Invalid token', 'message' => $e->getMessage()], 401);
        }

        return $next($request);
    }
}
