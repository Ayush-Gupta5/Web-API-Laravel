<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Controllers\API\AccountController;
use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\PostController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


//Open Route
Route::get('home', [HomeController::class, 'index'])->name('home');
Route::get('post/{id}', [HomeController::class, 'post'])->name('post');
Route::post('register', [AccountController::class, 'register'])->name('register');
Route::get('verifyEmail/{token}', [AccountController::class, 'verifyEmail'])->name('verifyEmail');
Route::post('forget-password', [AccountController::class, 'forgetPassword'])->name('forgetPassword');
Route::post('reset-password/{token}', [AccountController::class, 'resetPassword'])->name('resetPassword');
Route::post('login', [AccountController::class, 'login'])->name('login');

//Protected Route
Route::group(
    ['middleware' => [JwtMiddleware::class]],
    function () {

        Route::get('profile', [AccountController::class, 'profile'])->name('profile');
        Route::post('user-reset-password/', [AccountController::class, 'userResetPassword'])->name('userResetPassword');
        Route::apiResource('post', PostController::class);
        Route::post('comment/{id}', [HomeController::class, 'comment'])->name('comment');
        Route::post('update-comment/{id}', [HomeController::class, 'updateComment'])->name('updateComment');
        Route::delete('delete-comment/{id}', [HomeController::class, 'deleteComment'])->name('deleteComment');
    }
);
