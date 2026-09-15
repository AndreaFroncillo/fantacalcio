<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    'login' => [
        'eyebrow' => 'Welcome back',
        'title' => 'Sign in',
        'description' => 'Sign in to your Fantasy Football account.',
        'hero_title' => 'Your league is waiting.',
        'hero_description' => 'Manage your team, join live auctions and keep track of your budget, squad and competitions.',
        'hero_footer' => 'Your entire Fantasy Football experience, in one place.',
        'email' => 'Email',
        'password' => 'Password',
        'remember' => 'Remember me',
        'forgot_password' => 'Forgot your password?',
        'submit' => 'Sign in',
        'no_account' => "Don't have an account?",
        'register' => 'Sign up',
        'identifier' => 'Email or username',
        'logout' => 'Logout',
    ],

    'register' => [
        'eyebrow' => 'Create your account',
        'title' => 'Sign up',
        'description' => 'Create your account and get ready to join your league.',
        'hero_title' => 'Your Fantasy Football starts here.',
        'hero_description' => 'Create your profile, join your leagues and get ready for auctions, competitions and the transfer market, all in one place.',
        'hero_footer' => 'Your team. Your leagues. Your season.',
        'name' => 'First name',
        'surname' => 'Last name',
        'username' => 'Username',
        'email' => 'Email',
        'password' => 'Password',
        'password_confirmation' => 'Confirm password',
        'submit' => 'Create account',
        'already_registered' => 'Already have an account?',
        'login' => 'Sign in',
    ],

    'forgot' => [
        'eyebrow' => 'Account recovery',
        'title' => 'Forgot your password?',
        'description' => 'Enter your email and we will send you a link to reset your password.',
        'panel_title' => 'Get back in the game.',
        'panel_description' => 'Recover access to your account and get back to managing your team, auctions and competitions.',
        'panel_footer' => 'All you need is your account email address.',
        'submit' => 'Send recovery link',
        'back_to_login' => 'Back to login',
    ],

    'fields' => [
        'email' => 'Email',
        'password' => 'Password',
        'password_confirmation' => 'Confirm password',
    ],

    'reset' => [
        'eyebrow' => 'New password',
        'title' => 'Reset your password',
        'description' => 'Choose a new secure password for your account.',
        'panel_title' => 'Take back control.',
        'panel_description' => 'Set a new password and get back to your league, your team and the next auctions.',
        'panel_footer' => 'Choose a secure password that you do not use elsewhere.',
        'password' => 'New password',
        'password_confirmation' => 'Confirm new password',
        'submit' => 'Reset password',
        'back_to_login' => 'Back to login',
    ],

    'validation' => [
        'username_min' => 'The username must be at least 3 characters.',
        'username_max' => 'The username may not be greater than 30 characters.',
        'username_format' => 'The username may only contain letters, numbers, dashes and underscores.',
        'username_unique' => 'This username is already in use.',
        'email_invalid' => 'Enter a valid email address.',
        'email_unique' => 'This email is already associated with an account.',
        'password_confirmed' => 'The passwords do not match.',
        'username_available' => 'Username available.',
        'email_available' => 'Email available.',
    ],
];
