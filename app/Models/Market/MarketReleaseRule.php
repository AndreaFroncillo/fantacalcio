<?php

namespace App\Models\Market;

use Database\Factories\Market\MarketReleaseRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MarketReleaseRule extends Model
{
    /** @use HasFactory<MarketReleaseRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'market_session_id',
        'max_releases_per_team',
    ];

    protected static function booted(): void
    {
        static::creating(function (MarketReleaseRule $rule) {
            $rule->ulid ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'max_releases_per_team' => 'integer',
        ];
    }

    public function marketSession(): BelongsTo
    {
        return $this->belongsTo(MarketSession::class);
    }
}
