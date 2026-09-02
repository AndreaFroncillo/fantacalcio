<?php

namespace App\Models;

use App\Models\League\LeagueInvitation;
use App\Models\League\LeagueMembership;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'surname',
    'username',
    'email',
    'password',
    'avatar_path',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (! $user->ulid) {
                $user->ulid = (string) Str::ulid();
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
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function leagueMemberships(): HasMany
    {
        return $this->hasMany(LeagueMembership::class);
    }

    public function sentLeagueInvitations(): HasMany
    {
        return $this->hasMany(
            LeagueInvitation::class,
            'invited_by_user_id'
        );
    }
}
