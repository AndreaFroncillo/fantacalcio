<?php

namespace App\Models\Football;

use App\Domain\Football\Enums\FootballPlayerStatus;
use Database\Factories\Football\FootballPlayerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FootballPlayer extends Model
{
    /** @use HasFactory<FootballPlayerFactory> */
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'display_name',
        'date_of_birth',
        'nationality',
        'photo_path',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (FootballPlayer $footballPlayer) {
            $footballPlayer->ulid ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'status' => FootballPlayerStatus::class,
        ];
    }
}
