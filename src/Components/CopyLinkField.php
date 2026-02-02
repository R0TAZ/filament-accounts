<?php

namespace Rotaz\FilamentAccounts\Components;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\TextInput;

class CopyLinkField extends TextInput
{
    protected function copyClipboard($data, $livewire)
    {
        $livewire->js("navigator.clipboard.writeText('{$data}')");
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->suffixAction(
            Action::make('copyClipboard')
                ->label(__('filament-accounts::default.labels.link_copy'))
                ->icon('heroicon-m-clipboard')
                ->after(fn () => $this->hint(__('filament-accounts::default.general.info.link_copy_ok')))
                ->action(fn ($state, $livewire) => $this->copyClipboard($state, $livewire))
        );

    }
}
