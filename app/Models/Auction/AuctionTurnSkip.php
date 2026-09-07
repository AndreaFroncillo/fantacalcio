<?php

namespace App\Models\Auction;

use App\Domain\Auction\Enums\AuctionTurnSkipReason;
use App\Models\User;
use Database\Factories\Auction\AuctionTurnSkipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AuctionTurnSkip extends Model
{
    /** @use HasFactory<AuctionTurnSkipFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'auction_id',
        'auction_role_phase_id',
        'auction_participant_id',
        'turn_number',
        'reason',
        'performed_by_user_id',
        'note',
    ];

    protected static function booted(): void
    {
        static::creating(function (AuctionTurnSkip $auctionTurnSkip) {
            $auctionTurnSkip->ulid ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'turn_number' => 'integer',
            'reason' => AuctionTurnSkipReason::class,
        ];
    }

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function rolePhase(): BelongsTo
    {
        return $this->belongsTo(
            AuctionRolePhase::class,
            'auction_role_phase_id'
        );
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(
            AuctionParticipant::class,
            'auction_participant_id'
        );
    }

    public function performedByUser(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'performed_by_user_id'
        );
    }
}
