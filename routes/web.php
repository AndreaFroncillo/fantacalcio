<?php

use App\Models\Auction\Auction;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auctions/{auction}', function (Auction $auction) {
    Gate::authorize('view', $auction);

    return view('auction.show', [
        'auction' => $auction,
    ]);
})->name('auctions.show');
