<?php

namespace App\Models\League;

use Database\Factories\League\LeagueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'description',
    'timezone',
])]
class League extends Model
{
    /** @use HasFactory<LeagueFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (League $league) {
            if (! $league->ulid) {
                $league->ulid = (string) Str::ulid();
            }
        });
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(LeagueMembership::class);
    }
}
