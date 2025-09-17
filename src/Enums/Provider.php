<?php

namespace Rotaz\FilamentAccounts\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\View\View;
use Rotaz\FilamentAccounts\FilamentAccounts;

enum Provider: string implements HasLabel
{
    case Bitbucket = 'bitbucket';
    case Facebook = 'facebook';
    case Gitlab = 'gitlab';
    case Github = 'github';
    case Google = 'google';
    case LinkedIn = 'linkedin';
    case LinkedInOpenId = 'linkedin-openid';
    case Slack = 'slack';
    case Twitter = 'twitter';
    case TwitterOAuth2 = 'twitter-oauth-2';

    public function getLabel(): string
    {
        return match ($this) {
            self::Bitbucket => 'Bitbucket',
            self::Facebook => 'Facebook',
            self::Gitlab => 'GitLab',
            self::Github => 'GitHub',
            self::Google => 'Google',
            self::LinkedIn, self::LinkedInOpenId => 'LinkedIn',
            self::Slack => 'Slack',
            self::Twitter, self::TwitterOAuth2 => 'X',
        };
    }

    public function isEnabled(): bool
    {
        return FilamentAccounts::isProviderEnabled($this);
    }

    public function getIconView(): View
    {
        $viewName = match ($this) {
            self::Bitbucket => 'filament-accounts::components.socialite-icons.bitbucket',
            self::Facebook => 'filament-accounts::components.socialite-icons.facebook',
            self::Gitlab => 'filament-accounts::components.socialite-icons.gitlab',
            self::Github => 'filament-accounts::components.socialite-icons.github',
            self::Google => 'filament-accounts::components.socialite-icons.google',
            self::LinkedIn, self::LinkedInOpenId => 'filament-accounts::components.socialite-icons.linkedin',
            self::Slack => 'filament-accounts::components.socialite-icons.slack',
            self::Twitter, self::TwitterOAuth2 => 'filament-accounts::components.socialite-icons.twitter',
        };

        return view($viewName);
    }
}
