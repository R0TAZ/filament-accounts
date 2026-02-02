<?php

namespace Rotaz\FilamentAccounts\Concerns\Base;

use App\Models\Account;
use App\Models\AccountInvitation;
use App\Models\Party;
use App\Models\User;
use Rotaz\FilamentAccounts\BillingPlan;
use Rotaz\FilamentAccounts\Subscriber;
use Rotaz\FilamentAccounts\Subscription;
use Rotaz\FilamentAccounts\SubscriptionInvoice;

trait HasBaseModels
{

    public static string $userModel = User::class;

    public static string $accountModel = Account::class;

    public static string $partyModel = Party::class;

    public static string $accountInvitationModel = AccountInvitation::class;

    /**
     * Get the name of the user model used by the application.
     */
    public static function userModel(): string
    {
        return static::$userModel;
    }

    public static function accountModel(): string
    {
        return static::$accountModel;
    }

    public static function partyModel(): string
    {
        return static::$partyModel;
    }

    public static function accountInvitationModel(): string
    {
        return static::$accountInvitationModel;
    }

    public static function newUserModel(): mixed
    {
        $model = static::userModel();

        return new $model;
    }

    public static function newAccountModel(): mixed
    {
        $model = static::accountModel();

        return new $model;
    }

    public static function useUserModel(string $model): static
    {
        static::$userModel = $model;

        return new static;
    }

    public static function useAccountModel(string $model): static
    {
        static::$accountModel = $model;

        return new static;
    }

    public static function usePartyModel(string $model): static
    {
        static::$partyModel = $model;

        return new static;
    }

    public static function useAccountInvitationModel(string $model): static
    {
        static::$accountInvitationModel = $model;

        return new static;
    }

    /**
     * Find a user instance by the given ID.
     */
    public static function findUserByIdOrFail(int $id): mixed
    {
        return static::newUserModel()->where('id', $id)->firstOrFail();
    }

    /**
     * Find a user instance by the given email address or fail.
     */
    public static function findUserByEmailOrFail(string $email): mixed
    {
        return static::newUserModel()->where('email', $email)->firstOrFail();
    }

}
