<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Settings Driver
    |--------------------------------------------------------------------------
    |
    | Settings driver used to store persistent settings.
    |
    | Supported: "database"
    |
    */

    'default' => env('SETTINGS_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Enable / Disable caching
    |--------------------------------------------------------------------------
    |
    | If it is enabled all values gets cached after accessing it.
    |
    */
    'cache' => env('SETTING_CACHE', false),

    /*
    |--------------------------------------------------------------------------
    | Repositories Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the driver information for each repository that
    | is used by your application. A default configuration has been added
    | for each back-end shipped with this package. You are free to add more.
    |
    */

    'repositories' => [

        'database' => [
            'driver' => 'database',
            'connection' => env('DB_CONNECTION', 'mysql'),
            'table' => 'settings',
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => null,
            'use' => null,
            'prefix' => env('SETTINGS_PREFIX', 'setting'),
        ]

    ],


];
