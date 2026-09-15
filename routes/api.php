<?php

use App\Http\Controllers\Auction\Api\AuctionController;
use App\Http\Controllers\Auth\Api\RegistrationAvailabilityController;
use Illuminate\Support\Facades\Route;

Route::get(
    '/register/availability',
    RegistrationAvailabilityController::class
)->middleware('throttle:30,1')
    ->name('register.availability');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auctions/{auction}', [AuctionController::class, 'show']);
    Route::post('/auctions/{auction}/initialize', [AuctionController::class, 'initialize']);
    Route::post('/auctions/{auction}/start', [AuctionController::class, 'start']);
    Route::post('/auctions/{auction}/nominations', [AuctionController::class, 'startNomination']);
    Route::post('/auctions/{auction}/nominations/{nomination}/bids', [AuctionController::class, 'placeBid']);
    Route::post('/auctions/{auction}/nominations/{nomination}/confirm', [AuctionController::class, 'confirmNomination']);
    Route::post('/auctions/{auction}/nominations/{nomination}/reject', [AuctionController::class, 'rejectNomination']);
    Route::post('/auctions/{auction}/nominations/{nomination}/expire', [AuctionController::class, 'expireNomination']);
});
