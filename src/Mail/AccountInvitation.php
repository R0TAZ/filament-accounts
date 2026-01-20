<?php

namespace Rotaz\FilamentAccounts\Mail;

use App\Models\AccountInvitation as AccountInvitationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Rotaz\FilamentAccounts\FilamentAccounts;

class AccountInvitation extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * The account invitation instance.
     */
    public AccountInvitationModel $invitation;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(AccountInvitationModel $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Build the message.
     */
    public function build(): static
    {
        $acceptUrl = FilamentAccounts::generatePartyRegisterUrl($this->invitation);

        return $this->markdown('filament-accounts::mail.account-invitation', compact('acceptUrl'))
            ->subject(__('Account Invitation'));
    }
}
