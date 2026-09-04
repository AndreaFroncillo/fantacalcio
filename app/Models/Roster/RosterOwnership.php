<?php

namespace App\Models\Roster;

use App\Models\Football\PlayerSeason;
use App\Models\Season\LeagueSeason;
use App\Models\Team\Team;
use Database\Factories\Roster\RosterOwnershipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RosterOwnership extends Model
{
    /** @use HasFactory<RosterOwnershipFactory> */
    use HasFactory;

    protected $fillable = [
        'league_season_id',
        'team_id',
        'player_season_id',
        'acquisition_value',
        'acquired_at',
        'released_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (RosterOwnership $rosterOwnership) {
            $rosterOwnership->ulid ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'acquisition_value' => 'integer',
            'acquired_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function leagueSeason(): BelongsTo
    {
        return $this->belongsTo(LeagueSeason::class);
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
