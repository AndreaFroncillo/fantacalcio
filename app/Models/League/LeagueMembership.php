<?php

namespace App\Models\League;

use App\Domain\League\Enums\LeagueMembershipRole;
use App\Domain\League\Enums\LeagueMembershipStatus;
use App\Models\User;
use Database\Factories\League\LeagueMembershipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'league_id',
    'user_id',
    'role',
    'status',
    'joined_at',
])]
class LeagueMembership extends Model
{
    /** @use HasFactory<LeagueMembershipFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (LeagueMembership $membership) {
            if (! $membership->ulid) {
                $membership->ulid = (string) Str::ulid();
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => LeagueMembershipRole::class,
            'status' => LeagueMembershipStatus::class,
            'joined_at' => 'datetime',
        ];
    }

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
