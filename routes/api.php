<?php

use App\Http\Controllers\Auction\Api\AuctionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auctions/{auction}', [AuctionController::class, 'show']);
    Route::post('/auctions/{auction}/initialize', [AuctionController::class, 'initialize']);
    Route::post('/auctions/{auction}/start', [AuctionController::class, 'start']);
    Route::post('/auctions/{auction}/nominations', [AuctionController::class, 'startNomination']);
    Route::post('/auctions/{auction}/nominations/{nomination}/bids', [AuctionController::class, 'placeBid']);
    Route::post('/auctions/{auction}/nominations/{nomination}/confirm', [AuctionController::class, 'confirmNomination']);
    Route::post('/auctions/{auction}/nominations/{nomination}/reject', [AuctionController::class, 'rejectNomination']);
});
