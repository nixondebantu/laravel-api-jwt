<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auction\AuctionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// register and login routes

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// get user details with token
Route::get('user', [AuthController::class, 'getUserDetails']);

// update user details without id and registration number
Route::post('update-user-details', [AuthController::class, 'updateUserDetails']);

// Auction routes
Route::get('auctions', [AuctionController::class, 'index']);
Route::get('auctions/{id}', [AuctionController::class, 'show']);
Route::middleware('auth:api')->group(function () {
    Route::post('auctions', [AuctionController::class, 'store']);
    Route::put('auctions/{id}', [AuctionController::class, 'update']);
    Route::delete('auctions/{id}', [AuctionController::class, 'destroy']);
});
