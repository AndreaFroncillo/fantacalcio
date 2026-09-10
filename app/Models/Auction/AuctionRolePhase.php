<?php

namespace App\Models\Auction;

use App\Domain\Auction\Enums\AuctionRolePhaseStatus;
use App\Domain\Football\Enums\PlayerRole;
use Database\Factories\Auction\AuctionRolePhaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AuctionRolePhase extends Model
{
    /** @use HasFactory<AuctionRolePhaseFactory> */
    use HasFactory;

    protected $fillable = [
        'auction_id',
        'role',
        'position',
        'status',
        'started_at',
        'completed_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (AuctionRolePhase $auctionRolePhase) {
            $auctionRolePhase->ulid ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'role' => PlayerRole::class,
            'position' => 'integer',
            'status' => AuctionRolePhaseStatus::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function turnSkips(): HasMany
    {
        return $this->hasMany(AuctionTurnSkip::class);
    }

    public function nominations(): HasMany
    {
        return $this->hasMany(AuctionNomination::class);
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }
}
