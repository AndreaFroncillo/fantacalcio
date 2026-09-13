<?php

use App\Models\Auction\Auction;
use App\Models\Football\PlayerSeason;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/auctions/{auction}', function (Auction $auction) {
        Gate::authorize('view', $auction);

        $playerSeasons = PlayerSeason::query()
            ->with([
                'footballPlayer',
                'realClub',
            ])
            ->orderBy('id')
            ->get();

        return view('auction.show', [
            'auction' => $auction,
            'playerSeasons' => $playerSeasons,
        ]);
    })->name('auctions.show');
});
