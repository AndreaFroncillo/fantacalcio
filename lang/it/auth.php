<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | Le seguenti righe di lingua vengono utilizzate durante l'autenticazione
    | per i vari messaggi che dobbiamo mostrare all'utente. Sei libero di
    | modificare queste righe in base alle esigenze della tua applicazione.
    |
    */

    'failed' => 'Queste credenziali non corrispondono ai nostri dati.',
    'password' => 'La password fornita non è corretta.',
    'throttle' => 'Troppi tentativi di accesso. Riprova tra :seconds secondi.',

    'login' => [
        'eyebrow' => 'Bentornato',
        'title' => 'Accedi',
        'description' => 'Accedi al tuo account Fantacalcio.',
        'hero_title' => 'La tua lega ti aspetta.',
        'hero_description' => 'Gestisci la tua squadra, partecipa alle aste live e tieni sotto controllo budget, rosa e competizioni.',
        'hero_footer' => 'Tutta la tua esperienza Fantacalcio, in un unico posto.',
        'email' => 'Email',
        'password' => 'Password',
        'remember' => 'Ricordami',
        'forgot_password' => 'Hai dimenticato la password?',
        'submit' => 'Accedi',
        'no_account' => 'Non hai un account?',
        'register' => 'Registrati',
        'identifier' => 'Email o username',
    ],

    'register' => [
        'eyebrow' => 'Crea il tuo account',
        'title' => 'Registrati',
        'description' => 'Crea il tuo account e preparati a entrare nella tua lega.',
        'hero_title' => 'Il Fantacalcio parte da qui.',
        'hero_description' => 'Crea il tuo profilo, entra nelle tue leghe e preparati a vivere aste, competizioni e mercato in un unico posto.',
        'hero_footer' => 'La tua squadra. Le tue leghe. La tua stagione.',
        'name' => 'Nome',
        'surname' => 'Cognome',
        'username' => 'Username',
        'email' => 'Email',
        'password' => 'Password',
        'password_confirmation' => 'Conferma password',
        'submit' => 'Crea account',
        'already_registered' => 'Hai già un account?',
        'login' => 'Accedi',
    ],

    'forgot' => [
        'eyebrow' => 'Recupero account',
        'title' => 'Password dimenticata?',
        'description' => 'Inserisci la tua email e ti invieremo un link per reimpostare la password.',
        'panel_title' => 'Torniamo in campo.',
        'panel_description' => 'Recupera l’accesso al tuo account e torna a gestire squadra, aste e competizioni.',
        'panel_footer' => 'Ti basta l’indirizzo email del tuo account.',
        'submit' => 'Invia link di recupero',
        'back_to_login' => 'Torna al login',
    ],

    'fields' => [
        'email' => 'Email',
        'password' => 'Password',
        'password_confirmation' => 'Conferma password',
    ],

    'reset' => [
        'eyebrow' => 'Nuova password',
        'title' => 'Reimposta la password',
        'description' => 'Scegli una nuova password sicura per il tuo account.',
        'panel_title' => 'Riprendi il controllo.',
        'panel_description' => 'Imposta una nuova password e torna alla tua lega, alla tua squadra e alle prossime aste.',
        'panel_footer' => 'Scegli una password sicura che non utilizzi altrove.',
        'password' => 'Nuova password',
        'password_confirmation' => 'Conferma nuova password',
        'submit' => 'Reimposta password',
        'back_to_login' => 'Torna al login',
    ],

    'validation' => [
        'username_min' => 'L\'username deve contenere almeno 3 caratteri.',
        'username_max' => 'L\'username non può superare i 30 caratteri.',
        'username_format' => 'L\'username può contenere solo lettere, numeri, trattini e underscore.',
        'username_unique' => 'Questo username è già in uso.',
        'email_invalid' => 'Inserisci un indirizzo email valido.',
        'email_unique' => 'Questa email è già associata a un account.',
        'password_confirmed' => 'Le password non corrispondono.',
        'username_available' => 'Username disponibile.',
        'email_available' => 'Email disponibile.',
    ],
];
