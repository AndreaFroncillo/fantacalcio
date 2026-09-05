<?php

namespace App\Models\Market;

use App\Domain\Market\Enums\MarketSessionStatus;
use App\Models\Season\LeagueSeason;
use Database\Factories\Market\MarketSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MarketSession extends Model
{
    /** @use HasFactory<MarketSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'league_season_id',
        'name',
        'status',
        'starts_at',
        'ends_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (MarketSession $marketSession) {
            $marketSession->ulid ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'status' => MarketSessionStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function leagueSeason(): BelongsTo
    {
        return $this->belongsTo(LeagueSeason::class);
    }

    public function capabilities(): HasMany
    {
        return $this->hasMany(MarketCapability::class);
    }
}
