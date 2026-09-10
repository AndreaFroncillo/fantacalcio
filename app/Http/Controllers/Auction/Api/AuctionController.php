<?php

namespace App\Http\Controllers\Auction\Api;

use App\Domain\Auction\Actions\InitializeAuction;
use App\Domain\Auction\Actions\StartAuction;
use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Domain\Auction\Enums\AuctionRolePhaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Auction\Api\AuctionResource;
use App\Models\Auction\Auction;
use Illuminate\Support\Facades\Gate;
use RuntimeException;

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

    public function initialize(
        Auction $auction,
        InitializeAuction $initializeAuction
    ): AuctionResource {
        Gate::authorize('initialize', $auction);

        try {
            $auction = $initializeAuction->execute($auction);
        } catch (RuntimeException $exception) {
            abort(422, $exception->getMessage());
        }

        return new AuctionResource($auction);
    }

    public function start(
        Auction $auction,
        StartAuction $startAuction
    ): AuctionResource {
        Gate::authorize('start', $auction);

        try {
            $auction = $startAuction->execute($auction);
        } catch (RuntimeException $exception) {
            abort(422, $exception->getMessage());
        }

        return new AuctionResource($auction);
    }
}
