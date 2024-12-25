<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auction\AuctionController;
use App\Http\Controllers\Api\Team\TeamController;
use App\Http\Controllers\Api\Player\PlayerController;
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

Route::get('teams', [TeamController::class, 'index']);
Route::get('teams/{team}', [TeamController::class, 'show']);
Route::middleware('auth:api')->group(function () {
    Route::post('teams', [TeamController::class, 'store']);
    Route::put('teams/{team}', [TeamController::class, 'update']);
    Route::delete('teams/{team}', [TeamController::class, 'destroy']);
    Route::put('teams/{team}/accept', [TeamController::class, 'acceptTeam']);
    Route::get('teams/{team}/players', [TeamController::class, 'getTeamPlayers']);
});

// Player routes
Route::get('players', [PlayerController::class, 'index']);
Route::get('players/{player}', [PlayerController::class, 'show']);

Route::middleware('auth:api')->group(function () {
    Route::post('players/register', [PlayerController::class, 'register']);
    Route::put('players/{player}', [PlayerController::class, 'update']);
    Route::delete('players/{player}', [PlayerController::class, 'destroy']);
    Route::put('players/{player}/category', [PlayerController::class, 'assignCategory']);
});

// Auction Process Routes
Route::middleware('auth:api')->group(function () {
    Route::post('auctions/{auction}/start', [AuctionController::class, 'startAuction']);
    Route::post('auctions/{auction}/next', [AuctionController::class, 'nextPlayer']);
    Route::post('auctions/{auction}/bid', [AuctionController::class, 'placeBid']);
    Route::get('auctions/{auction}/state', [AuctionController::class, 'getAuctionState']);
});
