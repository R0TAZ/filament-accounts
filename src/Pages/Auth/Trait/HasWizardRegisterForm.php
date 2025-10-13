<?php

namespace Rotaz\FilamentAccounts\Pages\Auth\Trait;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Wizard;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Support\Enums\IconPosition;
use Joinapi\FilamentUtility\Form\Document;
use Joinapi\FilamentUtility\Form\PersonNameField;
use Joinapi\FilamentUtility\Form\PhoneNumber;
use Rotaz\FilamentAccounts\Utils\FormUtils;

trait HasWizardRegisterForm
{
    use HasWizard;

    public function getWizardFormSchema(): array
    {
        return [
            Wizard::make([
                Wizard\Step::make('INICIO')
                    ->schema([
                        ToggleButtons::make('account_type')
                            ->hiddenLabel()
                            ->options([
                                'personal' => 'Pessoal',
                                'business' => 'Empresarial',
                            ])
                            ->rule('required')
                            ->validationMessages([
                                'required' => 'ESCOLHA UMA OPÇÃO ACIMA',
                            ])
                            ->icons([
                                'personal' => 'heroicon-o-user',
                                'business' => 'heroicon-o-building-office',
                            ])
                            ->reactive()
                            ->inline()
                            ->view('filament-accounts::components.toggle-buttons.grouped')
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('is_personal', $state === 'personal');
                                $set('is_business', $state === 'business');
                            }),

                    ]),
                Wizard\Step::make('DADOS GERAIS')
                    ->schema(fn ($get) => $this->getRegisterFormSchema($get('account_type'))),
            ])->nextAction(fn () => $this->getActiveNextAction())
                ->submitAction($this->getRegisterFormAction()),

        ];

    }

    public function getWizardFormActions(): array
    {
        return [
            'submit' => 'Cadastrar',
        ];
    }

    public function getWizardFormStatePath(): string
    {
        return 'wizard';
    }

    public function getRegisterFormSchema($type): array
    {
        $schema = $type == 'personal' ? $this->getPersonalAccountFormSchema() : $this->getGroupAccountFormSchema();

        return array_merge($schema, $this->getCommonFormSchema());

    }

    public function getNextActionName(): string
    {
        return 'next';
    }

    public function getPersonalAccountFormSchema(): array
    {
        return [
            PersonNameField::make('company_contact')
                ->extraInputAttributes(FormUtils::getTextFormUpper())
                ->label('NOME'),
            Grid::make()
                ->schema([
                    PhoneNumber::make('phone')
                        ->format('(99)99999-9999')
                        ->label('TELEFONE'),
                    Document::make('document')
                        ->cpf()
                        ->label('CPF'),
                ]),

        ];

    }

    protected function getCommonFormSchema(): array
    {
        return [

            $this->getEmailFormComponent()
                ->label('E-MAIL')
                ->required(false),
            $this->getPasswordFormComponent()
                ->dehydrateStateUsing(fn ($state) => $state)
                ->label('SENHA'),
            $this->getPasswordConfirmationFormComponent()
                ->dehydrateStateUsing(fn ($state) => $state)
                ->label('CONFIRMAR SENHA'),

        ];

    }

    public function getPersonalAccountFormActions(): array
    {
        return [
            'submit' => 'Cadastrar',
        ];
    }

    public function getPersonalAccountFormStatePath(): string
    {
        return 'personalAccount';
    }

    public function getGroupAccountFormSchema(): array
    {
        return [
            PersonNameField::make('company_name')
                ->extraInputAttributes(FormUtils::getTextFormUpper())
                ->label('EMPRESA'),
            Grid::make()
                ->schema([
                    Document::make('document')
                        ->cnpj()
                        ->label('CNPJ'),
                    PersonNameField::make('company_contact')
                        ->extraInputAttributes(FormUtils::getTextFormUpper())
                        ->label('RESPONSAVEL'),
                ]),
        ];

    }

    public function getLegalPartyAccountFormActions(): array
    {
        return [
            'submit' => 'Cadastrar',
        ];
    }

    public function getLegalPartyAccountFormStatePath()
    {
        return 'legalPartyAccount';
    }

    public function getActiveNextAction(): \Filament\Forms\Components\Actions\Action
    {
        return \Filament\Forms\Components\Actions\Action::make($this->getNextActionName())
            ->label('Avançar')
            ->iconPosition(IconPosition::After)
            ->livewireClickHandlerEnabled(false)
            ->hidden(fn ($get) => empty($get('account_type')))
            ->livewireTarget('dispatchFormEvent')
            ->button();

    }
}
