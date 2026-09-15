<?php

namespace App\Http\Controllers\Auth\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RegistrationAvailabilityController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'field' => [
                'required',
                Rule::in([
                    'username',
                    'email',
                ]),
            ],
            'value' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $field = $validated['field'];

        $value = Str::lower(
            trim($validated['value'])
        );

        $available = ! User::query()
            ->where($field, $value)
            ->exists();

        return response()->json([
            'available' => $available,
        ]);
    }
}
