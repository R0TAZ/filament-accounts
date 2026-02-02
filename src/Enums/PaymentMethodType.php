<?php

namespace Rotaz\FilamentAccounts\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PaymentMethodType: string implements HasIcon, HasLabel
{
    case PIX_QRCODE = 'pix_qrcode';
    case PIX_LINK = 'pix_link';
    case CREDIT_CARD = 'credit_card';
    case BOLETO = 'boleto';
    case MONEY = 'money';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PIX_QRCODE => 'PIX Qrcode',
            self::PIX_LINK => 'PIX Link',
            self::CREDIT_CARD => 'Cartão de Crédito',
            self::BOLETO => 'Boleto',
            self::MONEY => 'Dinheiro',

        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PIX_QRCODE => 'heroicon-m-qr-code',
            self::PIX_LINK => 'heroicon-o-link',
            self::CREDIT_CARD => 'heroicon-m-credit-card',
            self::BOLETO => 'heroicon-m-newspaper',
            self::MONEY => 'heroicon-m-banknotes',
        };
    }
}
