<?php

namespace App\Models\League;

use App\Domain\League\Enums\LeagueInvitationStatus;
use App\Models\User;
use Database\Factories\League\LeagueInvitationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'league_id',
    'invited_by_user_id',
    'email',
    'status',
    'expires_at',
    'accepted_at',
    'revoked_at',
])]
class LeagueInvitation extends Model
{
    /** @use HasFactory<LeagueInvitationFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (LeagueInvitation $invitation) {
            if (! $invitation->ulid) {
                $invitation->ulid = (string) Str::ulid();
            }

            if (! $invitation->token) {
                $invitation->token = Str::random(64);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'status' => LeagueInvitationStatus::class,
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'invited_by_user_id'
        );
    }

    public function isExpired(): bool
    {
        return $this->status === LeagueInvitationStatus::PENDING
            && $this->expires_at->isPast();
    }
}
