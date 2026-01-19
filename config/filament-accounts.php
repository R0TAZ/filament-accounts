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
        'model' => \App\Models\User::class,
    ],
    'account' => [
        'model' => \App\Models\Account::class,
        'party_model' => \App\Models\Party::class,
        'invitations' => [
            'invite_mail_template' => \Rotaz\FilamentAccounts\Mail\AccountInvitation::class,
            'accept_url_callback' => fn () => filament()->getPanel('user')->getRegistrationUrl(),
            'expires_in_minutes' => 60 * 24 * 7, // 7 days
        ],
    ],

];
