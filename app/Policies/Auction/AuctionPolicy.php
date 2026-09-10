<?php

namespace App\Policies\Auction;

use App\Domain\League\Enums\LeagueMembershipRole;
use App\Domain\League\Enums\LeagueMembershipStatus;
use App\Models\Auction\Auction;
use App\Models\League\LeagueMembership;
use App\Models\User;

class AuctionPolicy
{
    public function view(User $user, Auction $auction): bool
    {
        $leagueId = $auction
            ->marketSession
            ->leagueSeason
            ->league_id;

        return LeagueMembership::query()
            ->where('league_id', $leagueId)
            ->where('user_id', $user->id)
            ->where('status', LeagueMembershipStatus::ACTIVE)
            ->exists();
    }

    public function nominate(User $user, Auction $auction): bool
    {
        $leagueId = $auction
            ->marketSession
            ->leagueSeason
            ->league_id;

        return LeagueMembership::query()
            ->where('league_id', $leagueId)
            ->where('user_id', $user->id)
            ->where('status', LeagueMembershipStatus::ACTIVE)
            ->exists();
    }

    public function initialize(User $user, Auction $auction): bool
    {
        $leagueId = $auction
            ->marketSession
            ->leagueSeason
            ->league_id;

        return LeagueMembership::query()
            ->where('league_id', $leagueId)
            ->where('user_id', $user->id)
            ->where('status', LeagueMembershipStatus::ACTIVE)
            ->where('role', LeagueMembershipRole::PRESIDENT)
            ->exists();
    }

    public function start(User $user, Auction $auction): bool
    {
        $leagueId = $auction
            ->marketSession
            ->leagueSeason
            ->league_id;

        return LeagueMembership::query()
            ->where('league_id', $leagueId)
            ->where('user_id', $user->id)
            ->where('status', LeagueMembershipStatus::ACTIVE)
            ->where('role', LeagueMembershipRole::PRESIDENT)
            ->exists();
    }

    public function bid(User $user, Auction $auction): bool
    {
        $leagueId = $auction
            ->marketSession
            ->leagueSeason
            ->league_id;

        return LeagueMembership::query()
            ->where('league_id', $leagueId)
            ->where('user_id', $user->id)
            ->where('status', LeagueMembershipStatus::ACTIVE)
            ->exists();
    }

    public function confirm(User $user, Auction $auction): bool
    {
        $leagueId = $auction
            ->marketSession
            ->leagueSeason
            ->league_id;

        return LeagueMembership::query()
            ->where('league_id', $leagueId)
            ->where('user_id', $user->id)
            ->where('role', LeagueMembershipRole::PRESIDENT)
            ->where('status', LeagueMembershipStatus::ACTIVE)
            ->exists();
    }

    public function reject(User $user, Auction $auction): bool
    {
        $leagueId = $auction
            ->marketSession
            ->leagueSeason
            ->league_id;

        return LeagueMembership::query()
            ->where('league_id', $leagueId)
            ->where('user_id', $user->id)
            ->where('role', LeagueMembershipRole::PRESIDENT)
            ->where('status', LeagueMembershipStatus::ACTIVE)
            ->exists();
    }

    public function expire(User $user, Auction $auction): bool
    {
        $leagueId = $auction
            ->marketSession
            ->leagueSeason
            ->league_id;

        return LeagueMembership::query()
            ->where('league_id', $leagueId)
            ->where('user_id', $user->id)
            ->where('status', LeagueMembershipStatus::ACTIVE)
            ->exists();
    }
}
