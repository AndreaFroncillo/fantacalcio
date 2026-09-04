<?php

namespace App\Models\Credit;

use App\Models\Team\Team;
use Database\Factories\Credit\TeamCreditAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TeamCreditAccount extends Model
{
    /** @use HasFactory<TeamCreditAccountFactory> */
    use HasFactory;

    protected $fillable = [
        'team_id',
        'initial_balance',
        'current_balance',
    ];

    protected static function booted(): void
    {
        static::creating(function (TeamCreditAccount $creditAccount) {
            $creditAccount->ulid ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'initial_balance' => 'integer',
            'current_balance' => 'integer',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }
}
