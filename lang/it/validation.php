<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Righe di lingua per la validazione
    |--------------------------------------------------------------------------
    |
    | Le seguenti righe di lingua contengono i messaggi di errore predefiniti
    | utilizzati dalla classe validator. Alcune di queste regole hanno più
    | versioni, come ad esempio le regole relative alle dimensioni.
    | Sei libero di modificare questi messaggi secondo le tue esigenze.
    |
    */

    'accepted' => 'Il campo :attribute deve essere accettato.',
    'accepted_if' => 'Il campo :attribute deve essere accettato quando :other è :value.',
    'active_url' => 'Il campo :attribute deve essere un URL valido.',
    'after' => 'Il campo :attribute deve essere una data successiva a :date.',
    'after_or_equal' => 'Il campo :attribute deve essere una data successiva o uguale a :date.',
    'alpha' => 'Il campo :attribute deve contenere solo lettere.',
    'alpha_dash' => 'Il campo :attribute deve contenere solo lettere, numeri, trattini e trattini bassi.',
    'alpha_num' => 'Il campo :attribute deve contenere solo lettere e numeri.',
    'any_of' => 'Il campo :attribute non è valido.',
    'array' => 'Il campo :attribute deve essere un array.',
    'array_keys' => 'Il campo :attribute deve contenere solo le seguenti chiavi: :values.',
    'ascii' => 'Il campo :attribute deve contenere solo caratteri alfanumerici e simboli a byte singolo.',
    'base64' => 'Il campo :attribute deve essere una stringa Base64 valida.',
    'before' => 'Il campo :attribute deve essere una data precedente a :date.',
    'before_or_equal' => 'Il campo :attribute deve essere una data precedente o uguale a :date.',

    'between' => [
        'array' => 'Il campo :attribute deve contenere tra :min e :max elementi.',
        'file' => 'Il campo :attribute deve avere una dimensione compresa tra :min e :max kilobyte.',
        'numeric' => 'Il campo :attribute deve essere compreso tra :min e :max.',
        'string' => 'Il campo :attribute deve contenere tra :min e :max caratteri.',
    ],

    'boolean' => 'Il campo :attribute deve essere vero o falso.',
    'can' => 'Il campo :attribute contiene un valore non autorizzato.',
    'confirmed' => 'La conferma del campo :attribute non corrisponde.',
    'contains' => 'Nel campo :attribute manca un valore obbligatorio.',
    'current_password' => 'La password non è corretta.',
    'date' => 'Il campo :attribute deve essere una data valida.',
    'date_equals' => 'Il campo :attribute deve essere una data uguale a :date.',
    'date_format' => 'Il campo :attribute deve corrispondere al formato :format.',
    'decimal' => 'Il campo :attribute deve avere :decimal cifre decimali.',
    'declined' => 'Il campo :attribute deve essere rifiutato.',
    'declined_if' => 'Il campo :attribute deve essere rifiutato quando :other è :value.',
    'different' => 'I campi :attribute e :other devono essere differenti.',
    'digits' => 'Il campo :attribute deve contenere :digits cifre.',
    'digits_between' => 'Il campo :attribute deve contenere tra :min e :max cifre.',
    'dimensions' => 'Il campo :attribute contiene un\'immagine con dimensioni non valide.',
    'distinct' => 'Il campo :attribute contiene un valore duplicato.',
    'doesnt_contain' => 'Il campo :attribute non deve contenere nessuno dei seguenti valori: :values.',
    'doesnt_end_with' => 'Il campo :attribute non deve terminare con uno dei seguenti valori: :values.',
    'doesnt_start_with' => 'Il campo :attribute non deve iniziare con uno dei seguenti valori: :values.',
    'email' => 'Il campo :attribute deve essere un indirizzo email valido.',
    'encoding' => 'Il campo :attribute deve essere codificato in :encoding.',
    'ends_with' => 'Il campo :attribute deve terminare con uno dei seguenti valori: :values.',
    'enum' => 'Il valore selezionato per :attribute non è valido.',
    'exists' => 'Il valore selezionato per :attribute non è valido.',
    'extensions' => 'Il campo :attribute deve avere una delle seguenti estensioni: :values.',
    'file' => 'Il campo :attribute deve essere un file.',
    'filled' => 'Il campo :attribute deve avere un valore.',

    'gt' => [
        'array' => 'Il campo :attribute deve contenere più di :value elementi.',
        'file' => 'Il campo :attribute deve avere una dimensione maggiore di :value kilobyte.',
        'numeric' => 'Il campo :attribute deve essere maggiore di :value.',
        'string' => 'Il campo :attribute deve contenere più di :value caratteri.',
    ],

    'gte' => [
        'array' => 'Il campo :attribute deve contenere almeno :value elementi.',
        'file' => 'Il campo :attribute deve avere una dimensione maggiore o uguale a :value kilobyte.',
        'numeric' => 'Il campo :attribute deve essere maggiore o uguale a :value.',
        'string' => 'Il campo :attribute deve contenere almeno :value caratteri.',
    ],

    'hex_color' => 'Il campo :attribute deve essere un colore esadecimale valido.',
    'image' => 'Il campo :attribute deve essere un\'immagine.',
    'in' => 'Il valore selezionato per :attribute non è valido.',
    'in_array' => 'Il campo :attribute deve essere presente in :other.',
    'in_array_keys' => 'Il campo :attribute deve contenere almeno una delle seguenti chiavi: :values.',
    'integer' => 'Il campo :attribute deve essere un numero intero.',
    'ip' => 'Il campo :attribute deve essere un indirizzo IP valido.',
    'ipv4' => 'Il campo :attribute deve essere un indirizzo IPv4 valido.',
    'ipv6' => 'Il campo :attribute deve essere un indirizzo IPv6 valido.',
    'json' => 'Il campo :attribute deve essere una stringa JSON valida.',
    'list' => 'Il campo :attribute deve essere una lista.',
    'lowercase' => 'Il campo :attribute deve essere in minuscolo.',

    'lt' => [
        'array' => 'Il campo :attribute deve contenere meno di :value elementi.',
        'file' => 'Il campo :attribute deve avere una dimensione inferiore a :value kilobyte.',
        'numeric' => 'Il campo :attribute deve essere inferiore a :value.',
        'string' => 'Il campo :attribute deve contenere meno di :value caratteri.',
    ],

    'lte' => [
        'array' => 'Il campo :attribute non deve contenere più di :value elementi.',
        'file' => 'Il campo :attribute deve avere una dimensione inferiore o uguale a :value kilobyte.',
        'numeric' => 'Il campo :attribute deve essere inferiore o uguale a :value.',
        'string' => 'Il campo :attribute deve contenere al massimo :value caratteri.',
    ],

    'mac_address' => 'Il campo :attribute deve essere un indirizzo MAC valido.',

    'max' => [
        'array' => 'Il campo :attribute non deve contenere più di :max elementi.',
        'file' => 'Il campo :attribute non deve avere una dimensione superiore a :max kilobyte.',
        'numeric' => 'Il campo :attribute non deve essere maggiore di :max.',
        'string' => 'Il campo :attribute non deve contenere più di :max caratteri.',
    ],

    'max_digits' => 'Il campo :attribute non deve contenere più di :max cifre.',
    'mimes' => 'Il campo :attribute deve essere un file di tipo: :values.',
    'mimetypes' => 'Il campo :attribute deve essere un file di tipo: :values.',

    'min' => [
        'array' => 'Il campo :attribute deve contenere almeno :min elementi.',
        'file' => 'Il campo :attribute deve avere una dimensione di almeno :min kilobyte.',
        'numeric' => 'Il campo :attribute deve essere almeno :min.',
        'string' => 'Il campo :attribute deve contenere almeno :min caratteri.',
    ],

    'min_digits' => 'Il campo :attribute deve contenere almeno :min cifre.',
    'missing' => 'Il campo :attribute non deve essere presente.',
    'missing_if' => 'Il campo :attribute non deve essere presente quando :other è :value.',
    'missing_unless' => 'Il campo :attribute non deve essere presente a meno che :other sia :value.',
    'missing_with' => 'Il campo :attribute non deve essere presente quando :values è presente.',
    'missing_with_all' => 'Il campo :attribute non deve essere presente quando :values sono presenti.',
    'multiple_of' => 'Il campo :attribute deve essere un multiplo di :value.',
    'not_in' => 'Il valore selezionato per :attribute non è valido.',
    'not_regex' => 'Il formato del campo :attribute non è valido.',
    'numeric' => 'Il campo :attribute deve essere un numero.',

    'password' => [
        'letters' => 'Il campo :attribute deve contenere almeno una lettera.',
        'mixed' => 'Il campo :attribute deve contenere almeno una lettera maiuscola e una minuscola.',
        'numbers' => 'Il campo :attribute deve contenere almeno un numero.',
        'symbols' => 'Il campo :attribute deve contenere almeno un simbolo.',
        'uncompromised' => 'Il valore di :attribute è comparso in una violazione di dati. Scegli un valore diverso per :attribute.',
    ],

    'present' => 'Il campo :attribute deve essere presente.',
    'present_if' => 'Il campo :attribute deve essere presente quando :other è :value.',
    'present_unless' => 'Il campo :attribute deve essere presente a meno che :other sia :value.',
    'present_with' => 'Il campo :attribute deve essere presente quando :values è presente.',
    'present_with_all' => 'Il campo :attribute deve essere presente quando :values sono presenti.',
    'prohibited' => 'Il campo :attribute non è consentito.',
    'prohibited_if' => 'Il campo :attribute non è consentito quando :other è :value.',
    'prohibited_if_accepted' => 'Il campo :attribute non è consentito quando :other è accettato.',
    'prohibited_if_declined' => 'Il campo :attribute non è consentito quando :other è rifiutato.',
    'prohibited_unless' => 'Il campo :attribute non è consentito a meno che :other non sia presente in :values.',
    'prohibits' => 'Il campo :attribute impedisce la presenza di :other.',
    'regex' => 'Il formato del campo :attribute non è valido.',
    'required' => 'Il campo :attribute è obbligatorio.',
    'required_array_keys' => 'Il campo :attribute deve contenere valori per: :values.',
    'required_if' => 'Il campo :attribute è obbligatorio quando :other è :value.',
    'required_if_accepted' => 'Il campo :attribute è obbligatorio quando :other è accettato.',
    'required_if_declined' => 'Il campo :attribute è obbligatorio quando :other è rifiutato.',
    'required_unless' => 'Il campo :attribute è obbligatorio a meno che :other non sia presente in :values.',
    'required_with' => 'Il campo :attribute è obbligatorio quando :values è presente.',
    'required_with_all' => 'Il campo :attribute è obbligatorio quando :values sono presenti.',
    'required_without' => 'Il campo :attribute è obbligatorio quando :values non è presente.',
    'required_without_all' => 'Il campo :attribute è obbligatorio quando nessuno dei valori :values è presente.',
    'same' => 'Il campo :attribute deve corrispondere a :other.',

    'size' => [
        'array' => 'Il campo :attribute deve contenere :size elementi.',
        'file' => 'Il campo :attribute deve avere una dimensione di :size kilobyte.',
        'numeric' => 'Il campo :attribute deve essere :size.',
        'string' => 'Il campo :attribute deve contenere :size caratteri.',
    ],

    'starts_with' => 'Il campo :attribute deve iniziare con uno dei seguenti valori: :values.',
    'string' => 'Il campo :attribute deve essere una stringa.',
    'timezone' => 'Il campo :attribute deve essere un fuso orario valido.',
    'unique' => 'Il valore di :attribute è già stato utilizzato.',
    'uploaded' => 'Il caricamento del campo :attribute non è riuscito.',
    'uppercase' => 'Il campo :attribute deve essere in maiuscolo.',
    'url' => 'Il campo :attribute deve essere un URL valido.',
    'ulid' => 'Il campo :attribute deve essere un ULID valido.',
    'uuid' => 'Il campo :attribute deve essere un UUID valido.',

    /*
    |--------------------------------------------------------------------------
    | Righe di lingua personalizzate per la validazione
    |--------------------------------------------------------------------------
    |
    | Qui puoi specificare messaggi di validazione personalizzati per gli
    | attributi utilizzando la convenzione "attributo.regola". In questo modo
    | puoi definire rapidamente un messaggio specifico per una determinata
    | regola di validazione.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'messaggio-personalizzato',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Attributi di validazione personalizzati
    |--------------------------------------------------------------------------
    |
    | Le seguenti righe di lingua vengono utilizzate per sostituire il
    | placeholder dell'attributo con un nome più leggibile, ad esempio
    | "Indirizzo email" al posto di "email". Questo permette di rendere
    | i messaggi di validazione più chiari per l'utente.
    |
    */

    'attributes' => [],

];
