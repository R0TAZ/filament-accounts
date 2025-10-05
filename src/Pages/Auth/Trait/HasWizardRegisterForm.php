<?php

namespace Rotaz\FilamentAccounts\Pages\Auth\Trait;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Wizard;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Support\Enums\IconPosition;

trait HasWizardRegisterForm
{
    use HasWizard;

    public function getWizardFormSchema()
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

    public function getWizardFormActions()
    {
        return [
            'submit' => 'Cadastrar',
        ];
    }

    public function getWizardFormStatePath()
    {
        return 'wizard';
    }

    public function getRegisterFormSchema($type)
    {
        $schema = $type == 'personal' ? $this->getPersonalAccountFormSchema() : $this->getGroupAccountFormSchema();

        return array_merge($schema, $this->getCommonFormSchema());

    }

    public function getNextActionName(): string
    {
        return 'next';
    }

    public function getPersonalAccountFormSchema()
    {
        return [
            $this->getNameFormComponent()
                ->label('NOME'),
            TextInput::make('phone')
                ->label('TELEFONE')
                ->tel()
                ->mask('(99) 99999-9999')
                ->maxLength(255),
            TextInput::make('cpf')
                ->label('CPF')
                ->maxLength(255),

        ];

    }

    protected function getCommonFormSchema()
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

    public function getPersonalAccountFormActions()
    {
        return [
            'submit' => 'Cadastrar',
        ];
    }

    public function getPersonalAccountFormStatePath()
    {
        return 'personalAccount';
    }

    public function getGroupAccountFormSchema()
    {
        return [
            $this->getNameFormComponent()
                ->label('NOME'),
            TextInput::make('tax_id')
                ->label('CNPJ')
                ->maxLength(255),
            TextInput::make('company_contact')
                ->label('RESPONSAVEL')
                ->maxLength(255),
        ];

    }

    public function getLegalPartyAccountFormActions()
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
