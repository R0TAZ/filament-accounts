@component('mail::message')
{{ __('You have been invited to join the :account account!', ['account' => $invitation->account->name]) }}

{{ __('You may accept this invitation by clicking the button below:') }}

@component('mail::button', ['url' => $acceptUrl])
{{ __('Accept Invitation') }}
@endcomponent

{{ __('If you did not expect to receive an invitation to this account, you may discard this email.') }}
@endcomponent
