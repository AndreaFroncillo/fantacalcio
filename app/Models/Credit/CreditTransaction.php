<?php

namespace App\Models\Credit;

use App\Domain\Credit\Enums\CreditTransactionType;
use Database\Factories\Credit\CreditTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CreditTransaction extends Model
{
    /** @use HasFactory<CreditTransactionFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'team_credit_account_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
    ];

    protected static function booted(): void
    {
        static::creating(function (CreditTransaction $creditTransaction) {
            $creditTransaction->ulid ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'type' => CreditTransactionType::class,
            'amount' => 'integer',
            'balance_before' => 'integer',
            'balance_after' => 'integer',
        ];
    }

    public function teamCreditAccount(): BelongsTo
    {
        return $this->belongsTo(TeamCreditAccount::class);
    }
}
