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
    'billing' => [
        'trial_days' => env('BILLING_TRIAL_DAYS', 30),
        'pix_key'    => env('BILLING_PIX_KEY', ''),
        'bank' => [
            'acct_id'     => env('BILLING_BANK_ACCT_ID', '001'),
            'acct_number' => env('BILLING_BANK_ACCT_NUMBER', '001'),
            'acct_name'   => env('BILLING_BANK_ACCT_NAME', ''),
        ],
    ],

];
