<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option controls the default authentication "guard" and password
    | reset options for your application. You may change these defaults
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'api' => 'api',
        'guard' => 'admin.web',
        'passwords' => 'users',
    ],

    'guards'       => [

        'user'   => [
            'web' => [
                'driver'   => 'session',
                'provider' => 'users',
            ],

            'api' => [
                'driver'   => 'token',
                'provider' => 'users',
            ],
        ],

        'admin'  => [
            'web' => [
                'driver'   => 'session',
                'provider' => 'admins',
            ],

            'api' => [
                'driver'   => 'token',
                'provider' => 'admins',
            ],

        ],
        /*
        'client' => [
            'web' => [
                'driver'   => 'session',
                'provider' => 'clients',
            ],

            'api' => [
                'driver'   => 'token',
                'provider' => 'clients',
            ],

        ],
        */
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication drivers have a user provider. This defines how the
    | users are actually retrieved out of your database or other storage
    | mechanisms used by this application to persist your user's data.
    |
    | If you have multiple user tables or models you may configure multiple
    | sources which represent each model / table. These sources may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
     */

    'providers'    => [
        'users'   => [
            'driver' => 'eloquent',
            'model'  => App\Models\User::class,
        ],
        'admins'   => [
            'driver' => 'eloquent',
            'model'  => App\Models\AdminUser::class,
        ],
        'clients' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Client::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | Here you may set the options for resetting passwords including the view
    | that is your password reset e-mail. You may also set the name of the
    | table that maintains all of the reset tokens for your application.
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | seperate password reset settings based on the specific user types.
    |
    | The expire time is the number of minutes that the reset token should be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
     */

    'passwords'    => [
        'user'   => [
            'provider' => 'users',
            'table'    => 'password_resets',
            'expire'   => 60,
        ],

        'admin'  => [
            'provider' => 'users',
            'table'    => 'password_resets',
            'expire'   => 60,
        ],

        'client' => [
            'provider' => 'clients',
            'table'    => 'password_resets',
            'expire'   => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | You may specify multiple password reset configurations if you have more
    | than one user table or model in the application and you want to have
    | separate password reset settings based on the specific user types.
    |
    | The expire time is the number of minutes that the reset token should be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
        ],
    ],

];
