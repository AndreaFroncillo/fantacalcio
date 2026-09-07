<?php

namespace App\Models\Auction;

use App\Models\Team\Team;
use Database\Factories\Auction\AuctionParticipantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AuctionParticipant extends Model
{
    /** @use HasFactory<AuctionParticipantFactory> */
    use HasFactory;

    protected $fillable = [
        'auction_id',
        'team_id',
        'nomination_position',
    ];

    protected static function booted(): void
    {
        static::creating(function (AuctionParticipant $auctionParticipant) {
            $auctionParticipant->ulid ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'nomination_position' => 'integer',
        ];
    }

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function turnSkips(): HasMany
    {
        return $this->hasMany(AuctionTurnSkip::class);
    }

    public function nominations(): HasMany
    {
        return $this->hasMany(AuctionNomination::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(AuctionBid::class);
    }
}
