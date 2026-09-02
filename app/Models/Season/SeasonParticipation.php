<?php

namespace App\Models\Season;

use App\Domain\Season\Enums\SeasonParticipationStatus;
use App\Models\League\LeagueMembership;
use App\Models\Team\Team;
use Database\Factories\Season\SeasonParticipationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class SeasonParticipation extends Model
{
    /** @use HasFactory<SeasonParticipationFactory> */
    use HasFactory;

    protected $fillable = [
        'league_season_id',
        'league_membership_id',
        'status',
        'joined_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (SeasonParticipation $seasonParticipation) {
            $seasonParticipation->ulid ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'status' => SeasonParticipationStatus::class,
            'joined_at' => 'datetime',
        ];
    }

    public function leagueSeason(): BelongsTo
    {
        return $this->belongsTo(LeagueSeason::class);
    }

    public function leagueMembership(): BelongsTo
    {
        return $this->belongsTo(LeagueMembership::class);
    }

    public function team(): HasOne
    {
        return $this->hasOne(Team::class);
    }
}
