<?php

namespace Rotaz\FilamentAccounts\Http\Livewire;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\TextInput;

class PixLinkField extends TextInput
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
                ->icon('heroicon-m-clipboard')
                ->after(fn () => $this->hint('O item foi copiado com sucesso!'))
                ->action(fn ($state, $livewire) => $this->copyClipboard($state, $livewire))
        );
        $this->hintColor('warning');
    }
}
