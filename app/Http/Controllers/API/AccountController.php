<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Mail\VerifyEmail;
use Illuminate\Http\Request;
use App\Mail\forgetPasswordMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AccountController extends Controller
{
    public function register(Request $request)
    {
        $validateData = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $payload = [
            'user_id' => $user->id,
            'email' => $user->email,
        ];

        $key = env('JWT_SECRET');

        $token = JWT::encode($payload, $key, 'HS256');

        Mail::to($user->email)->send(new VerifyEmail($token));

        return response()->json([
            'status' => true,
            'message' => 'User registered successfully',
            'data' => $validateData
        ]);
    }

    public function verifyEmail($token)
    {
        try {
            $key = env('JWT_SECRET');
            $decode = JWT::decode($token, new Key($key, 'HS256'));

            $user = User::find($decode->user_id);

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found'
                ], 404);
            }
            if ($user->email_verified == true) {
                return response()->json([
                    'message' => 'Email already verified'
                ]);
            }
            $user->email_verified = true;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Email verified successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid token'
            ], 400);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->email_verified != '1') {
                return response()->json(['message' => 'Please verify your email']);
            }

            // Update last login time
            $update = User::find($user->id);
            $update->last_login = Carbon::now()->setTimezone('Asia/Kolkata');
            $update->save();

            $payload = [
                'user_id' => $user->id,
                'email' => $user->email,
                'iat' => time(), // Time when JWT was issued.
                'exp' => time() + 7200 // Expiration time (2 hour from issued time)
            ];

            $key = env('JWT_SECRET');

            $token = JWT::encode($payload, $key, 'HS256');

            return response()->json([
                'token' => $token,
                'user_id' => $user->id
            ]);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }


    public function profile(Request $request)
    {
        $token = $request->header('Authorization');
        $key = env('JWT_SECRET');
        $data = JWT::decode($token, new Key($key, 'HS256'));
        $user = User::find($data->user_id);

        return response()->json(['message' => 'success', 'data' => $user]);
    }

    public function forgetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->email;
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ]);
        }

        $name = $user->name;
        $payload = [
            'name' => $user->name,
            'user_id' => $user->id,
            'email' => $user->email,
            'iat' => time(), // Time when JWT was issued.
            'exp' => time() + 3600 // Expiration time (1 hour from issued time)
        ];
        $key = env('JWT_SECRET');
        $token = JWT::encode($payload, $key, 'HS256');

        Mail::to($user->email)->send(new forgetPasswordMail($token, $name));

        return response()->json([
            'message' => 'Reset password link successfully send to your mail.'
        ]);
    }

    public function resetPassword(Request $request, $token)
    {
        $request->validate([
            'new_password' => 'required|confirmed'
        ]);

        $key = env('JWT_SECRET');
        $credentials = JWT::decode($token, new Key($key, 'HS256'));
        $user = User::find($credentials->user_id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $newPassword = bcrypt($request->new_password);
        $user->password = $newPassword;
        $user->save();

        return response()->json([
            'message' => 'Password reset sccessfully'
        ]);
    }

    public function userResetPassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed'
        ]);
        $token = $request->header('Authorization');
        $key = env('JWT_SECRET');
        $credentials = JWT::decode($token, new Key($key, 'HS256'));

        $user = User::find($credentials->user_id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['error' => 'The old password does not match our records.'], 400);
        }

        // Hash and set the new password
        $user->password = bcrypt($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password successfully updated.']);
    }
}
