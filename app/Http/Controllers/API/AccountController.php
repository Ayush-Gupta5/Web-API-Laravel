<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Carbon\Carbon;
use App\Mail\VerifyEmail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AccountController extends Controller
{
    public function register(Request $request)
    {
        $validateData=$request->validate([
            'name'=>'required|string',
            'email'=>'required|email|unique:users,email',
            'password'=>'required|confirmed'
        ]);

        $user=User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>bcrypt($request->password)
        ]);

        $payload=[
            'user_id'=>$user->id,
            'email'=>$user->email,
            'iat'=>time(), //Time when JWT was issued.
            'exp'=>time() + 3600 //Expiration time (1 hour from issued time)
        ];

        $key=env('JWT_SECRET');

        $token=JWT::encode($payload,$key,'HS256');

        Mail::to($user->email)->send(new VerifyEmail($token));

        return response()->json([
            'status'=>true,
            'message'=>'User registered successfully',
            'data'=>$validateData
        ]);

    }

    public function verifyEmail($token){
        try{
            $key=env('JWT_SECRET');
            $decode=JWT::decode($token,new Key($key,'HS256'));

            $user=User::find($decode->user_id);

            if(!$user){
                return response()->json([
                    'status'=>false,
                    'message'=>'User not found'
                ],404);
            }
            if($user->email_verified == true){
                return response()->json([
                    'message'=>'Email already verified'
                ] );
            }
            $user->email_verified = true;
            $user->save();

            return response()->json([
                'status'=>true,
                'message'=>'Email verified successfully'
            ]);
        }catch(\Exception $e){
            return response()->json([
                'status'=>false,
                'message'=>'Invalid token'
            ],400);
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
            return response()->json(['message' => 'Your email is not verified']);
        }

        // Update last login time
        $update=User::find($user->id);
        $update->last_login = Carbon::now()->setTimezone('Asia/Kolkata');
        $update->save();

        $payload = [
            'user_id' => $user->id,
            'email' => $user->email,
            'iat' => time(), // Time when JWT was issued.
            'exp' => time() + 3600 // Expiration time (1 hour from issued time)
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
        $token=$request->header('Authorization');
        $key=env('JWT_SECRET');
        $data=JWT::decode($token,new Key($key,'HS256'));
        $user=User::find($data->user_id);

            return response()->json(['message'=>'success','data'=> $user]);
    }

    public function refreshToken()
    {

    }

    public function logout()
    {

    }
}
