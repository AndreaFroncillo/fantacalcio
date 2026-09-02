<?php

namespace App\Models\Team;

use App\Domain\Team\Enums\TeamStatus;
use App\Models\Credit\TeamCreditAccount;
use App\Models\Season\SeasonParticipation;
use Database\Factories\Team\TeamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Team extends Model
{
    /** @use HasFactory<TeamFactory> */
    use HasFactory;

    protected $fillable = [
        'season_participation_id',
        'name',
        'short_name',
        'logo_path',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (Team $team) {
            $team->ulid ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'status' => TeamStatus::class,
        ];
    }

    public function seasonParticipation(): BelongsTo
    {
        return $this->belongsTo(SeasonParticipation::class);
    }

    public function creditAccount(): HasOne
    {
        return $this->hasOne(TeamCreditAccount::class);
    }
}
