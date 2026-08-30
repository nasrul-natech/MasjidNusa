<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Demo Accounts (Local Development Only)
    |--------------------------------------------------------------------------
    |
    | Shown on the login page when APP_ENV=local for quick testing.
    | Never displayed in staging or production environments.
    |
    */

    'accounts' => [
        [
            'role' => 'Super Admin',
            'email' => 'superadmin@masjidnusa.id',
            'password' => 'password',
            'color' => 'purple',
        ],
        [
            'role' => 'Admin Operasional',
            'email' => 'operasional@masjidnusa.id',
            'password' => 'password',
            'color' => 'blue',
        ],
        [
            'role' => 'Admin Keuangan',
            'email' => 'keuangan@masjidnusa.id',
            'password' => 'password',
            'color' => 'amber',
        ],
        [
            'role' => 'Admin Media IT',
            'email' => 'mediait@masjidnusa.id',
            'password' => 'password',
            'color' => 'cyan',
        ],
        [
            'role' => 'Jamaah',
            'email' => 'jamaah@masjidnusa.id',
            'password' => 'password',
            'color' => 'emerald',
        ],
    ],

];
