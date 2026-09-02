<?php

namespace App\Models\Football;

use App\Domain\Football\Enums\FootballSeasonStatus;
use Database\Factories\Football\FootballSeasonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FootballSeason extends Model
{
    /** @use HasFactory<FootballSeasonFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'start_year',
        'end_year',
        'status',
        'starts_at',
        'ends_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (FootballSeason $footballSeason) {
            $footballSeason->ulid ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'start_year' => 'integer',
            'end_year' => 'integer',
            'status' => FootballSeasonStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function playerSeasons(): HasMany
    {
        return $this->hasMany(PlayerSeason::class);
    }
}
