<?php

namespace App\Models\Football;

use App\Domain\Football\Enums\PlayerRole;
use App\Domain\Football\Enums\PlayerSeasonStatus;
use Database\Factories\Football\PlayerSeasonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PlayerSeason extends Model
{
    /** @use HasFactory<PlayerSeasonFactory> */
    use HasFactory;

    protected $fillable = [
        'football_season_id',
        'football_player_id',
        'real_club_id',
        'role',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (PlayerSeason $playerSeason) {
            $playerSeason->ulid ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'role' => PlayerRole::class,
            'status' => PlayerSeasonStatus::class,
        ];
    }

    public function footballSeason(): BelongsTo
    {
        return $this->belongsTo(FootballSeason::class);
    }

    public function footballPlayer(): BelongsTo
    {
        return $this->belongsTo(FootballPlayer::class);
    }

    public function realClub(): BelongsTo
    {
        return $this->belongsTo(RealClub::class);
    }
}
