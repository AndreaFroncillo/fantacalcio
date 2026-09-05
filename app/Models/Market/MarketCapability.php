<?php

namespace App\Models\Market;

use App\Domain\Market\Enums\MarketCapabilityType;
use Database\Factories\Market\MarketCapabilityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MarketCapability extends Model
{
    /** @use HasFactory<MarketCapabilityFactory> */
    use HasFactory;

    protected $fillable = [
        'market_session_id',
        'type',
        'is_enabled',
    ];

    protected static function booted(): void
    {
        static::creating(function (MarketCapability $marketCapability) {
            $marketCapability->ulid ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'type' => MarketCapabilityType::class,
            'is_enabled' => 'boolean',
        ];
    }

    public function marketSession(): BelongsTo
    {
        return $this->belongsTo(MarketSession::class);
    }
}
