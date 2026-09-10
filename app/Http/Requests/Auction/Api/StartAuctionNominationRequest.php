<?php

namespace App\Http\Requests\Auction\Api;

use Illuminate\Foundation\Http\FormRequest;

class StartAuctionNominationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'player_season_ulid' => [
                'required',
                'string',
                'exists:player_seasons,ulid',
            ],
        ];
    }
}
