<?php

namespace App\Models\Season;

use App\Domain\Season\Enums\LeagueSeasonStatus;
use App\Models\League\League;
use Database\Factories\Season\LeagueSeasonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'league_id',
    'start_year',
    'end_year',
    'status',
    'currency',
    'max_teams',
    'initial_credits',
    'starts_at',
    'ends_at',
])]
class LeagueSeason extends Model
{
    /** @use HasFactory<LeagueSeasonFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (LeagueSeason $season) {
            if (! $season->ulid) {
                $season->ulid = (string) Str::ulid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'status' => LeagueSeasonStatus::class,
            'start_year' => 'integer',
            'end_year' => 'integer',
            'max_teams' => 'integer',
            'initial_credits' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }
}
