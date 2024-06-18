<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AccountController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


//Open Route
Route::post('register',[AccountController::class,'register'])->name('register');
Route::post('login',[AccountController::class,'login'])->name('login');
Route::get('verifyEmail/{token}',[AccountController::class,'verifyEmail'])->name('verifyEmail');

//Protected Route
Route::group(['middleware'=>['auth:api']],
function(){

    Route::get('profile',[AccountController::class,'profile'])->name('profile');
    Route::get('refresh-token',[AccountController::class,'refreshToken'])->name('refreshToken');
    Route::get('logout',[AccountController::class,'logout'])->name('logout');
});