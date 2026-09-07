<?php

namespace App\Models\Market;

use App\Models\Football\PlayerSeason;
use App\Models\Roster\RosterOwnership;
use App\Models\Team\Team;
use Database\Factories\Market\PlayerReleaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PlayerRelease extends Model
{
    /** @use HasFactory<PlayerReleaseFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'market_session_id',
        'roster_ownership_id',
        'team_id',
        'player_season_id',
        'release_value',
        'released_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (PlayerRelease $playerRelease) {
            $playerRelease->ulid ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'release_value' => 'integer',
            'released_at' => 'datetime',
        ];
    }

    public function marketSession(): BelongsTo
    {
        return $this->belongsTo(MarketSession::class);
    }

    public function rosterOwnership(): BelongsTo
    {
        return $this->belongsTo(RosterOwnership::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function playerSeason(): BelongsTo
    {
        return $this->belongsTo(PlayerSeason::class);
    }
}
