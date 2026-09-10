<?php

namespace App\Models\Auction;

use Database\Factories\Auction\AuctionBidFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AuctionBid extends Model
{
    /** @use HasFactory<AuctionBidFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'auction_nomination_id',
        'auction_participant_id',
        'amount',
        'sequence_number',
        'placed_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (AuctionBid $auctionBid) {
            $auctionBid->ulid ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'sequence_number' => 'integer',
            'placed_at' => 'datetime',
        ];
    }

    public function nomination(): BelongsTo
    {
        return $this->belongsTo(
            AuctionNomination::class,
            'auction_nomination_id'
        );
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(
            AuctionParticipant::class,
            'auction_participant_id'
        );
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }
}
