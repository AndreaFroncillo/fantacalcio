<?php

namespace App\Http\Resources\Auction\Api;

use App\Models\Auction\AuctionParticipant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuctionResource extends JsonResource
{
    public ?AuctionParticipant $currentParticipant = null;

    public function toArray(Request $request): array
    {
        $activeRolePhase = $this->rolePhases->first();
        $activeNomination = $this->nominations->first();
        $currentBid = $activeNomination?->currentBid;
        $nominator = $activeNomination?->participant;

        return [
            'ulid' => $this->ulid,
            'status' => $this->status->value,
            'base_timer_seconds' => $this->base_timer_seconds,
            'bid_extension_seconds' => $this->bid_extension_seconds,
            'current_participant_ulid' => $this->currentParticipant?->ulid,

            'permissions' => [
                'can_confirm_nomination' => $request
                    ->user()
                    ->can('confirm', $this->resource),

                'can_reject_nomination' => $request
                    ->user()
                    ->can('reject', $this->resource),
            ],

            'active_role_phase' => $activeRolePhase
                ? [
                    'ulid' => $activeRolePhase->ulid,
                    'role' => $activeRolePhase->role->value,
                    'position' => $activeRolePhase->position,
                    'status' => $activeRolePhase->status->value,
                ]
                : null,

            'active_nomination' => $activeNomination
                ? [
                    'ulid' => $activeNomination->ulid,
                    'player_season_ulid' => $activeNomination->playerSeason->ulid,
                    'turn_number' => $activeNomination->turn_number,
                    'opening_price' => $activeNomination->opening_price,
                    'status' => $activeNomination->status->value,
                    'timer_started_at' => $activeNomination->timer_started_at->toISOString(),
                    'expires_at' => $activeNomination->expires_at->toISOString(),

                    'player' => [
                        'player_season_ulid' => $activeNomination->playerSeason->ulid,
                        'football_player_ulid' => $activeNomination->playerSeason->footballPlayer->ulid,
                        'display_name' => $activeNomination->playerSeason->footballPlayer->display_name,
                        'role' => $activeNomination->playerSeason->role->value,
                        'real_club' => [
                            'ulid' => $activeNomination->playerSeason->realClub->ulid,
                            'name' => $activeNomination->playerSeason->realClub->name,
                            'short_name' => $activeNomination->playerSeason->realClub->short_name,
                        ],
                    ],

                    'nominator' => $nominator
                        ? [
                            'ulid' => $nominator->ulid,
                            'nomination_position' => $nominator->nomination_position,
                        ]
                        : null,

                    'current_bid' => $currentBid
                        ? [
                            'ulid' => $currentBid->ulid,
                            'amount' => $currentBid->amount,
                            'sequence_number' => $currentBid->sequence_number,
                            'participant_ulid' => $currentBid->participant->ulid,
                            'placed_at' => $currentBid->placed_at->toISOString(),
                        ]
                        : null,

                ]
                : null,

            'participants' => $this->participants
                ->map(function ($participant) {
                    return [
                        'ulid' => $participant->ulid,
                        'nomination_position' => $participant->nomination_position,
                        'team' => [
                            'ulid' => $participant->team->ulid,
                            'name' => $participant->team->name,
                            'short_name' => $participant->team->short_name,
                            'current_balance' => $participant->team->creditAccount->current_balance,
                        ],
                    ];
                })
                ->values(),
        ];
    }
}
