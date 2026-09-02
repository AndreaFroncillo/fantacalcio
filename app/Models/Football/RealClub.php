<?php

namespace App\Models\Football;

use App\Domain\Football\Enums\RealClubStatus;
use Database\Factories\Football\RealClubFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RealClub extends Model
{
    /** @use HasFactory<RealClubFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'slug',
        'logo_path',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (RealClub $realClub) {
            $realClub->ulid ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'status' => RealClubStatus::class,
        ];
    }

    public function playerSeasons(): HasMany
    {
        return $this->hasMany(PlayerSeason::class);
    }
}
