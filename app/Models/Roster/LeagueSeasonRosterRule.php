<?php

namespace App\Models\Roster;

use App\Domain\Football\Enums\PlayerRole;
use App\Models\Season\LeagueSeason;
use Database\Factories\Roster\LeagueSeasonRosterRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LeagueSeasonRosterRule extends Model
{
    /** @use HasFactory<LeagueSeasonRosterRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'league_season_id',
        'role',
        'max_players',
    ];

    protected static function booted(): void
    {
        static::creating(function (LeagueSeasonRosterRule $rosterRule) {
            $rosterRule->ulid ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'role' => PlayerRole::class,
            'max_players' => 'integer',
        ];
    }

    public function leagueSeason(): BelongsTo
    {
        return $this->belongsTo(LeagueSeason::class);
    }
}
