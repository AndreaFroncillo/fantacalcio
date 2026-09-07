<?php

namespace App\Models\Market;

use App\Domain\Market\Enums\MarketCapabilityEventType;
use App\Models\User;
use Database\Factories\Market\MarketCapabilityEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MarketCapabilityEvent extends Model
{
    /** @use HasFactory<MarketCapabilityEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'market_capability_id',
        'type',
        'performed_by_user_id',
        'reason',
    ];

    protected static function booted(): void
    {
        static::creating(function (MarketCapabilityEvent $event) {
            $event->ulid ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'type' => MarketCapabilityEventType::class,
        ];
    }

    public function marketCapability(): BelongsTo
    {
        return $this->belongsTo(MarketCapability::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_user_id');
    }
}
