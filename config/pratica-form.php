<?php

return [
    'required_fields' => [
        'nome' => true,
        'tipologia' => false,
        'teams' => false,
        'competenza' => false,
        'ruolo_generale' => false,
        'giudice' => false,
        'stato' => false,
    ],

    'documenti' => [
        'disk' => env('PRATICA_DOCUMENTI_DISK', 'documenti'),
        'directory' => env('PRATICA_DOCUMENTI_PATH', 'documenti-pratiche'),
        'allowed_extensions' => env('PRATICA_DOCUMENTI_ALLOWED_EXTENSIONS', ['pdf,doc,docx']),
        'max_size' => env('PRATICA_DOCUMENTI_MAX_SIZE', 102400), // KB
        'nas' => [
            'chunk_size' => env('NAS_UPLOAD_CHUNK_SIZE', 1024 * 1024), // 1MB chunks
            'timeout' => env('NAS_TIMEOUT', 300), // 5 minutes
            'retry_times' => env('NAS_RETRY_TIMES', 3),
            'retry_interval' => env('NAS_RETRY_INTERVAL', 1000), // milliseconds
        ],
    ],

    'tipologie' => [
        'Civile' => 'Civile',
        'Penale' => 'Penale',
        'Amministrativo' => 'Amministrativo',
        'Tributario' => 'Tributario',
    ],

    'stati' => [
        'aperto' => 'Aperto',
        'chiuso' => 'Chiuso',
        'sospeso' => 'Sospeso',
    ],

    'priorita' => [
        'alta' => 'Alta',
        'media' => 'Media',
        'bassa' => 'Bassa',
    ],

    'tipi_udienza' => [
        'prima_comparizione' => 'Prima Comparizione',
        'istruttoria' => 'Istruttoria',
        'decisoria' => 'Decisoria',
        'discussione' => 'Discussione',
        'altro' => 'Altro',
    ],

    'visibilita_note' => [
        'privata' => 'Privata',
        'pubblica' => 'Pubblica',
    ],

    'tipologie_note' => [
        'registro_contabile' => 'Registro Contabile',
        'annotazioni' => 'Annotazioni',
    ],

    //entrata
    //uscita
    'tipi_contabilita' => [
        'entrata' => 'Entrata',
        'uscita' => 'Uscita',
    ],
];