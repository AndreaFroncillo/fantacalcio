<?php

namespace App\Http\Controllers\Auction\Api;

use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Domain\Auction\Enums\AuctionRolePhaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Auction\Api\AuctionResource;
use App\Models\Auction\Auction;
use Illuminate\Support\Facades\Gate;

class AuctionController extends Controller
{
    public function show(Auction $auction): AuctionResource
    {
        Gate::authorize('view', $auction);

        $auction->load([
            'rolePhases' => fn ($query) => $query
                ->where('status', AuctionRolePhaseStatus::ACTIVE)
                ->orderBy('position'),

            'nominations' => fn ($query) => $query
                ->where('status', AuctionNominationStatus::ACTIVE)
                ->with([
                    'playerSeason.footballPlayer',
                    'playerSeason.realClub',
                    'participant',
                    'currentBid.participant',
                ])
                ->orderByDesc('id'),

            'participants' => fn ($query) => $query
                ->with('team.creditAccount')
                ->orderBy('nomination_position'),
        ]);

        return new AuctionResource($auction);
    }
}
