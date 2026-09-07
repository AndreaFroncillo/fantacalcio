<?php

namespace App\Domain\Auction\Actions;

use App\Domain\Auction\Enums\AuctionRolePhaseStatus;
use App\Domain\Auction\Enums\AuctionStatus;
use App\Domain\Football\Enums\PlayerRole;
use App\Domain\Market\Enums\MarketCapabilityType;
use App\Domain\Season\Enums\SeasonParticipationStatus;
use App\Domain\Team\Enums\TeamStatus;
use App\Models\Auction\Auction;
use App\Models\Auction\AuctionParticipant;
use App\Models\Auction\AuctionRolePhase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InitializeAuction
{
    public function execute(Auction $auction): Auction
    {
        return DB::transaction(function () use ($auction) {
            $auction = Auction::query()
                ->whereKey($auction->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->validateAuction($auction);

            $teams = $this->getEligibleTeams($auction);

            if ($teams->isEmpty()) {
                throw new RuntimeException(
                    'Auction cannot be initialized without active teams.'
                );
            }

            $this->createRolePhases($auction);
            $this->createParticipants($auction, $teams);

            return $auction->fresh([
                'rolePhases',
                'participants',
            ]);
        });
    }

    private function validateAuction(Auction $auction): void
    {
        if ($auction->status !== AuctionStatus::SCHEDULED) {
            throw new RuntimeException(
                'Only scheduled auctions can be initialized.'
            );
        }

        if (
            $auction->rolePhases()->exists()
            || $auction->participants()->exists()
        ) {
            throw new RuntimeException(
                'Auction has already been initialized.'
            );
        }

        $marketSession = $auction->marketSession;

        $hasAuctionCapability = $marketSession
            ->capabilities()
            ->where('type', MarketCapabilityType::AUCTION->value)
            ->where('is_enabled', true)
            ->exists();

        if (! $hasAuctionCapability) {
            throw new RuntimeException(
                'Auction capability is not enabled.'
            );
        }
    }

    private function getEligibleTeams(Auction $auction): Collection
    {
        return $auction
            ->marketSession
            ->leagueSeason
            ->participations()
            ->where(
                'status',
                SeasonParticipationStatus::ACTIVE->value
            )
            ->whereHas('team', function ($query) {
                $query->where(
                    'status',
                    TeamStatus::ACTIVE->value
                );
            })
            ->with('team')
            ->get()
            ->pluck('team');
    }

    private function createRolePhases(Auction $auction): void
    {
        $roles = [
            PlayerRole::GOALKEEPER,
            PlayerRole::DEFENDER,
            PlayerRole::MIDFIELDER,
            PlayerRole::FORWARD,
        ];

        foreach ($roles as $index => $role) {
            AuctionRolePhase::create([
                'auction_id' => $auction->id,
                'role' => $role,
                'position' => $index + 1,
                'status' => AuctionRolePhaseStatus::PENDING,
            ]);
        }
    }

    private function createParticipants(
        Auction $auction,
        Collection $teams
    ): void {
        $teams = $teams->shuffle()->values();

        foreach ($teams as $index => $team) {
            AuctionParticipant::create([
                'auction_id' => $auction->id,
                'team_id' => $team->id,
                'nomination_position' => $index + 1,
            ]);
        }
    }
}
