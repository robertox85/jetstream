<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [



        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

       //  'pratiche' => [
       //      'driver' => 'local',
       //      'root' => storage_path('app/private/pratiche'),
       //      'visibility' => 'private',
       //  ],

        'pratiche' => [
            'driver' => 'ftp',
            'host' => env('FTP_HOST', 'ftp.example.com'),
            'username' => env('FTP_USERNAME'),
            'password' => env('FTP_PASSWORD'),
            'port' => env('FTP_PORT', 21),
            'root' => env('FTP_ROOT', '/'),
            'passive' => false,
            'ssl' => false,
            'timeout' => 30,
            'visibility' => 'private',
        ],

        'nas' => [
            'driver' => 'sftp', // oppure 'ftp' se il NAS non supporta SFTP
            'host' => env('NAS_HOST', '192.168.1.100'),
            'username' => env('NAS_USERNAME', 'your-username'),
            'password' => env('NAS_PASSWORD', 'your-password'),
            'port' => env('NAS_PORT', 22),
            'root' => env('NAS_ROOT', '/path/to/documents'),
            'visibility' => 'private',
            'directory_visibility' => 'private',
            // Opzionale: configurazione SSL/TLS
            'ssl' => env('NAS_SSL', true),
            'timeout' => 30,
            'throw' => true,
        ],

        'remote_ftp' => [
            'driver' => 'ftp',
            'host' => env('REMOTE_HOST', 'ftp.example.com'),
            'username' => env('REMOTE_USERNAME'),
            'password' => env('REMOTE_PASSWORD'),
            'port' => env('REMOTE_PORT', 21),
            'root' => env('REMOTE_ROOT', '/'),
            'passive' => true,
            'ssl' => true,
            'timeout' => 30,
        ],

        'remote_sftp' => [
            'driver' => 'sftp',
            'host' => env('REMOTE_HOST', 'sftp.example.com'),
            'username' => env('REMOTE_USERNAME'),
            'password' => env('REMOTE_PASSWORD'),
            'port' => env('REMOTE_PORT', 22),
            'root' => env('REMOTE_ROOT', '/'),
            'visibility' => 'private',
            'timeout' => 30,
            'throw' => true,
        ]

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
