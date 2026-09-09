<?php

use App\Http\Controllers\Auction\Api\AuctionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auctions/{auction}', [AuctionController::class, 'show']);
});
