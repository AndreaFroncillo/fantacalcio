<?php

namespace App\Models\Auction;

use App\Domain\Auction\Enums\AuctionStatus;
use App\Models\Market\MarketSession;
use Database\Factories\Auction\AuctionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Auction extends Model
{
    /** @use HasFactory<AuctionFactory> */
    use HasFactory;

    protected $fillable = [
        'market_session_id',
        'status',
        'base_timer_seconds',
        'bid_extension_seconds',
        'started_at',
        'completed_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (Auction $auction) {
            $auction->ulid ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'status' => AuctionStatus::class,
            'base_timer_seconds' => 'integer',
            'bid_extension_seconds' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function marketSession(): BelongsTo
    {
        return $this->belongsTo(MarketSession::class);
    }

    public function rolePhases(): HasMany
    {
        return $this->hasMany(AuctionRolePhase::class)
            ->orderBy('position');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(AuctionParticipant::class)
            ->orderBy('nomination_position');
    }

    public function turnSkips(): HasMany
    {
        return $this->hasMany(AuctionTurnSkip::class)
            ->orderBy('turn_number');
    }

    public function nominations(): HasMany
    {
        return $this->hasMany(AuctionNomination::class)
            ->orderBy('turn_number');
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }
}
