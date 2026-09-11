<?php

namespace App\Domain\Auction\Actions;

use App\Domain\Auction\Enums\AuctionRolePhaseStatus;
use App\Domain\Auction\Enums\AuctionStatus;
use App\Domain\Football\Enums\PlayerRole;
use App\Domain\Market\Enums\MarketCapabilityType;
use App\Domain\Market\Enums\MarketSessionStatus;
use App\Events\Auction\AuctionStarted;
use App\Models\Auction\Auction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StartAuction
{
    public function execute(Auction $auction): Auction
    {
        return DB::transaction(function () use ($auction) {
            $auction = Auction::query()
                ->whereKey($auction->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->validateAuction($auction);

            $firstPhase = $auction->rolePhases()
                ->where('position', 1)
                ->lockForUpdate()
                ->firstOrFail();

            $startedAt = now();

            $firstPhase->update([
                'status' => AuctionRolePhaseStatus::ACTIVE,
                'started_at' => $startedAt,
            ]);

            $auction->update([
                'status' => AuctionStatus::LIVE,
                'started_at' => $startedAt,
            ]);

            $auction = $auction->fresh([
                'rolePhases',
                'participants',
            ]);

            AuctionStarted::dispatch($auction);

            return $auction;
        });
    }

    private function validateAuction(Auction $auction): void
    {
        if ($auction->status !== AuctionStatus::SCHEDULED) {
            throw new RuntimeException(
                'Only scheduled auctions can be started.'
            );
        }

        $marketSession = $auction->marketSession;

        if ($marketSession->status !== MarketSessionStatus::OPEN) {
            throw new RuntimeException(
                'Market session must be open before starting the auction.'
            );
        }

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

        if (! $auction->participants()->exists()) {
            throw new RuntimeException(
                'Auction has no participants.'
            );
        }

        $phases = $auction->rolePhases()
            ->orderBy('position')
            ->get();

        if ($phases->count() !== 4) {
            throw new RuntimeException(
                'Auction role phases are not correctly initialized.'
            );
        }

        $expectedRoles = [
            PlayerRole::GOALKEEPER,
            PlayerRole::DEFENDER,
            PlayerRole::MIDFIELDER,
            PlayerRole::FORWARD,
        ];

        foreach ($expectedRoles as $index => $expectedRole) {
            $phase = $phases[$index];

            if (
                $phase->position !== $index + 1
                || $phase->role !== $expectedRole
                || $phase->status !== AuctionRolePhaseStatus::PENDING
            ) {
                throw new RuntimeException(
                    'Auction role phases are not correctly initialized.'
                );
            }
        }
    }
}
