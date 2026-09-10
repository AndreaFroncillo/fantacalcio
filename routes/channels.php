<?php

use App\Domain\League\Enums\LeagueMembershipStatus;
use App\Models\Auction\Auction;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('auction.{auction}', function (User $user, Auction $auction) {
    return $auction
        ->marketSession
        ->leagueSeason
        ->league
        ->memberships()
        ->where('user_id', $user->id)
        ->where('status', LeagueMembershipStatus::ACTIVE)
        ->exists();
});
