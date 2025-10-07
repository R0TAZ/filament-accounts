<?php

return [
    'auth' => [
        'guard' => 'web',
        'default_password_length' => 12,
        'default_password_strength' => 1,
        'identifiers' => [
            'email' => true,
            'username' => false,
            'phone' => false,
            'social' => false,
            'other' => false,
        ],
        'model' => Filabiz\Security\Services\Auth\AbstractFilabizUser::class,
    ],

];
