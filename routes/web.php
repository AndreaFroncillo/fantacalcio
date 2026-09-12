<?php

use App\Models\Auction\Auction;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auctions/{auction}', function (Auction $auction) {
    return view('auction.show', [
        'auction' => $auction,
    ]);
})->name('auctions.show');
