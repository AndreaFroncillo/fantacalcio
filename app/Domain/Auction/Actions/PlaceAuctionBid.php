<?php

namespace App\Domain\Auction\Actions;

use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Domain\Auction\Enums\AuctionStatus;
use App\Models\Auction\AuctionBid;
use App\Models\Auction\AuctionNomination;
use App\Models\Auction\AuctionParticipant;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PlaceAuctionBid
{
    public function execute(
        AuctionNomination $nomination,
        AuctionParticipant $participant,
        int $amount
    ): AuctionBid {
        return DB::transaction(function () use (
            $nomination,
            $participant,
            $amount
        ) {
            $nomination = AuctionNomination::query()
                ->lockForUpdate()
                ->findOrFail($nomination->id);

            $auction = $nomination->auction()
                ->lockForUpdate()
                ->firstOrFail();

            if ($auction->status !== AuctionStatus::LIVE) {
                throw new RuntimeException(
                    'Auction is not live.'
                );
            }

            if ($nomination->status !== AuctionNominationStatus::ACTIVE) {
                throw new RuntimeException(
                    'Auction nomination is not active.'
                );
            }

            if (
                $nomination->expires_at &&
                $nomination->expires_at->isPast()
            ) {
                throw new RuntimeException(
                    'Auction nomination timer has expired.'
                );
            }

            if ($participant->auction_id !== $nomination->auction_id) {
                throw new RuntimeException(
                    'Participant does not belong to the nomination auction.'
                );
            }

            $lastBid = $nomination->bids()
                ->orderByDesc('sequence_number')
                ->first();

            if (
                ! $lastBid &&
                $amount < $nomination->opening_price
            ) {
                throw new RuntimeException(
                    'Bid amount must be at least the opening price.'
                );
            }

            if (
                $lastBid &&
                $lastBid->auction_participant_id === $participant->id
            ) {
                throw new RuntimeException(
                    'Participant cannot bid twice consecutively.'
                );
            }

            if (
                $lastBid &&
                $amount <= $lastBid->amount
            ) {
                throw new RuntimeException(
                    'Bid amount must be greater than the current bid.'
                );
            }

            $creditAccount = $participant->team
                ->creditAccount()
                ->lockForUpdate()
                ->first();

            if (! $creditAccount) {
                throw new RuntimeException(
                    'Auction participant team has no credit account.'
                );
            }

            if ($amount > $creditAccount->current_balance) {
                throw new RuntimeException(
                    'Bid amount exceeds participant available credits.'
                );
            }

            $sequenceNumber = ($lastBid?->sequence_number ?? 0) + 1;

            $now = now();

            $remainingSeconds = max(
                0,
                $nomination->expires_at->getTimestamp() - $now->getTimestamp()
            );

            $newRemainingSeconds = min(
                $remainingSeconds + $auction->bid_extension_seconds,
                $auction->base_timer_seconds
            );

            $nomination->update([
                'expires_at' => $now->copy()->addSeconds($newRemainingSeconds),
            ]);

            return $nomination->bids()->create([
                'auction_participant_id' => $participant->id,
                'amount' => $amount,
                'sequence_number' => $sequenceNumber,
                'placed_at' => now(),
            ]);
        });
    }
}
