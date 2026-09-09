<?php

namespace App\Models\Auction;

use App\Domain\Auction\Enums\AuctionNominationCloseReason;
use App\Domain\Auction\Enums\AuctionNominationStatus;
use App\Models\Football\PlayerSeason;
use App\Models\User;
use Database\Factories\Auction\AuctionNominationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class AuctionNomination extends Model
{
    /** @use HasFactory<AuctionNominationFactory> */
    use HasFactory;

    protected $fillable = [
        'auction_id',
        'auction_role_phase_id',
        'auction_participant_id',
        'player_season_id',
        'turn_number',
        'status',
        'opening_price',
        'timer_started_at',
        'expires_at',
        'closed_at',
        'closed_by_user_id',
        'close_reason',
    ];

    protected static function booted(): void
    {
        static::creating(function (AuctionNomination $auctionNomination) {
            $auctionNomination->ulid ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'turn_number' => 'integer',
            'status' => AuctionNominationStatus::class,
            'opening_price' => 'integer',
            'timer_started_at' => 'datetime',
            'expires_at' => 'datetime',
            'closed_at' => 'datetime',
            'close_reason' => AuctionNominationCloseReason::class,
        ];
    }

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function rolePhase(): BelongsTo
    {
        return $this->belongsTo(
            AuctionRolePhase::class,
            'auction_role_phase_id'
        );
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(
            AuctionParticipant::class,
            'auction_participant_id'
        );
    }

    public function playerSeason(): BelongsTo
    {
        return $this->belongsTo(PlayerSeason::class);
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'closed_by_user_id'
        );
    }

    public function bids(): HasMany
    {
        return $this->hasMany(AuctionBid::class)
            ->orderBy('sequence_number');
    }

    public function currentBid(): HasOne
    {
        return $this->hasOne(AuctionBid::class)
            ->ofMany('sequence_number', 'max');
    }
}
