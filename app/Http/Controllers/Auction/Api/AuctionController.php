<?php

namespace App\Http\Controllers\Auction\Api;

use App\Domain\Auction\Actions\ExpireAuctionNomination;
use App\Domain\Auction\Actions\FinalizeAndProgressAuction;
use App\Domain\Auction\Actions\InitializeAuction;
use App\Domain\Auction\Actions\PlaceAuctionBid;
use App\Domain\Auction\Actions\StartAuction;
use App\Domain\Auction\Actions\StartAuctionNomination;
use App\Domain\Auction\Enums\AuctionNominationCloseReason;
use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Domain\Auction\Enums\AuctionRolePhaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auction\Api\PlaceAuctionBidRequest;
use App\Http\Requests\Auction\Api\StartAuctionNominationRequest;
use App\Http\Resources\Auction\Api\AuctionResource;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionNomination;
use App\Models\Auction\AuctionParticipant;
use App\Models\Football\PlayerSeason;
use Illuminate\Support\Facades\Gate;
use RuntimeException;

class AuctionController extends Controller
{
    public function show(Auction $auction): AuctionResource
    {
        Gate::authorize('view', $auction);

        $auction->load([
            'rolePhases' => fn($query) => $query
                ->where('status', AuctionRolePhaseStatus::ACTIVE)
                ->orderBy('position'),

            'nominations' => fn($query) => $query
                ->where('status', AuctionNominationStatus::ACTIVE)
                ->with([
                    'playerSeason.footballPlayer',
                    'playerSeason.realClub',
                    'participant',
                    'currentBid.participant',
                ])
                ->orderByDesc('id'),

            'participants' => fn($query) => $query
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

    public function startNomination(
        StartAuctionNominationRequest $request,
        Auction $auction,
        StartAuctionNomination $startAuctionNomination
    ) {
        Gate::authorize('nominate', $auction);

        $playerSeason = PlayerSeason::query()
            ->where('ulid', $request->validated('player_season_ulid'))
            ->firstOrFail();

        try {
            $nomination = $startAuctionNomination->execute(
                $auction,
                $playerSeason,
                $request->user()
            );

            if ($nomination === null) {
                abort(
                    422,
                    'Auction has no eligible participant for the active role phase.'
                );
            }
        } catch (RuntimeException $exception) {
            abort(422, $exception->getMessage());
        }

        return response()->json([
            'data' => [
                'ulid' => $nomination->ulid,
                'player' => [
                    'player_season_ulid' => $nomination
                        ->playerSeason
                        ->ulid,
                ],
            ],
        ]);
    }

    public function placeBid(
        PlaceAuctionBidRequest $request,
        Auction $auction,
        AuctionNomination $nomination,
        PlaceAuctionBid $placeAuctionBid
    ) {
        Gate::authorize('bid', $auction);

        if ($nomination->auction_id !== $auction->id) {
            abort(404);
        }

        $participant = AuctionParticipant::query()
            ->where('auction_id', $auction->id)
            ->whereHas(
                'team.seasonParticipation.leagueMembership',
                function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                }
            )
            ->first();

        if ($participant === null) {
            abort(422, 'User is not an auction participant.');
        }

        try {
            $bid = $placeAuctionBid->execute(
                $nomination,
                $participant,
                $request->integer('amount')
            );
        } catch (RuntimeException $exception) {
            abort(422, $exception->getMessage());
        }

        return response()->json([
            'data' => [
                'ulid' => $bid->ulid,
                'amount' => $bid->amount,
                'sequence_number' => $bid->sequence_number,
            ],
        ]);
    }

    public function confirmNomination(
        Auction $auction,
        AuctionNomination $nomination,
        FinalizeAndProgressAuction $finalizeAndProgressAuction
    ) {
        Gate::authorize('confirm', $auction);

        if ($nomination->auction_id !== $auction->id) {
            abort(404);
        }

        try {
            $finalizeAndProgressAuction->execute(
                $nomination,
                AuctionNominationCloseReason::PRESIDENT_CONFIRMED,
                request()->user()
            );
        } catch (RuntimeException $exception) {
            abort(422, $exception->getMessage());
        }

        $nomination->refresh();

        return response()->json([
            'data' => [
                'ulid' => $nomination->ulid,
                'status' => $nomination->status->value,
                'close_reason' => $nomination->close_reason?->value,
                'closed_at' => $nomination->closed_at?->toISOString(),
            ],
        ]);
    }

    public function rejectNomination(
        Auction $auction,
        AuctionNomination $nomination,
        FinalizeAndProgressAuction $finalizeAndProgressAuction
    ) {
        Gate::authorize('reject', $auction);

        if ($nomination->auction_id !== $auction->id) {
            abort(404);
        }

        try {
            $finalizeAndProgressAuction->execute(
                $nomination,
                AuctionNominationCloseReason::PRESIDENT_REJECTED,
                request()->user()
            );
        } catch (RuntimeException $exception) {
            abort(422, $exception->getMessage());
        }

        $nomination->refresh();

        return response()->json([
            'data' => [
                'ulid' => $nomination->ulid,
                'status' => $nomination->status->value,
                'close_reason' => $nomination->close_reason?->value,
                'closed_at' => $nomination->closed_at?->toISOString(),
            ],
        ]);
    }

    public function expireNomination(
        Auction $auction,
        AuctionNomination $nomination,
        ExpireAuctionNomination $expireAuctionNomination
    ) {
        Gate::authorize('expire', $auction);

        if ($nomination->auction_id !== $auction->id) {
            abort(404);
        }

        try {
            $expireAuctionNomination->execute($nomination);
        } catch (RuntimeException $exception) {
            abort(422, $exception->getMessage());
        }

        $nomination->refresh();

        return response()->json([
            'data' => [
                'ulid' => $nomination->ulid,
                'status' => $nomination->status->value,
                'close_reason' => $nomination->close_reason?->value,
                'closed_at' => $nomination->closed_at?->toISOString(),
            ],
        ]);
    }
}
