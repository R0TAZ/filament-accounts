<?php
namespace Rotaz\FilamentBilling\Livewire;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\View;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;


class ViewSubscription extends Component  implements  HasInfolists, HasForms
{
    use InteractsWithInfolists;
    use InteractsWithForms;

    public mixed $subscription;

    public function mount(mixed $subscription): void
    {
        $this->subscription = $subscription;
    }

    public function subscriptionInfolist(Infolist $infolist): Infolist{
        return $infolist->schema([
            Grid::make(3)->schema([
                TextEntry::make('plan.name')->label('Name'),
                TextEntry::make('plan.description')->label('Plan description'),
                TextEntry::make('plan.features')
                    ->label('Features')
                    ->badge()
                    ->separator(','),
                ]),
            Grid::make(3)->schema([
                TextEntry::make('amount')
                    ->money('BRL'),
                TextEntry::make('ends_at')
                    ->date('d/m/Y'),
                TextEntry::make('status'),
            ]),
            Grid::make(3)->schema([
                TextEntry::make('cycle'),
                TextEntry::make('created_at')
                    ->date('d/m/Y H:i'),
                TextEntry::make('updated_at')
                    ->date('d/m/Y H:i'),
            ]),
        ])->record( $this->subscription);
    }

    public function render()
    {
        return view('filament-billing::livewire.view-subscription');
    }
}
