<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Support\PersonNameNormalizer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        $input['name'] = PersonNameNormalizer::normalize(
            $input['name'] ?? ''
        );

        $input['surname'] = PersonNameNormalizer::normalize(
            $input['surname'] ?? ''
        );

        $input['username'] = Str::lower(
            $input['username'] ?? ''
        );

        $input['email'] = Str::lower(
            $input['email'] ?? ''
        );

        Validator::make(
            $input,
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'surname' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'username' => [
                    'required',
                    'string',
                    'min:3',
                    'max:30',
                    'alpha_dash:ascii',
                    Rule::unique(User::class),
                ],

                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique(User::class),
                ],

                'password' => $this->passwordRules(),
            ],
            [
                'username.min' => __('auth.validation.username_min'),
                'username.max' => __('auth.validation.username_max'),
                'username.alpha_dash' => __('auth.validation.username_format'),
                'username.unique' => __('auth.validation.username_unique'),

                'email.email' => __('auth.validation.email_invalid'),
                'email.unique' => __('auth.validation.email_unique'),

                'password.confirmed' => __('auth.validation.password_confirmed'),
            ]
        )->validate();

        return User::create([
            'name' => $input['name'],
            'surname' => $input['surname'],
            'username' => $input['username'],
            'email' => $input['email'],
            'password' => Hash::make(
                $input['password']
            ),
        ]);
    }
}
