<?php

namespace Rotaz\FilamentAccounts\Actions;

use Closure;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Rotaz\FilamentAccounts\Components\CopyLinkField;
use Rotaz\FilamentAccounts\Components\QrCodeField;
use Rotaz\FilamentAccounts\Enums\SubscriptionInvoiceStatus;

class PaymentViewAction extends Action
{
    use CanCustomizeProcess;

    protected ?Closure $mutateRecordDataUsing = null;

    protected ?string $selectedPayment = null;

    public static function getDefaultName(): ?string
    {
        return 'payment_view';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->button();

        $this->form($this->getFormSchema());

        $this->modalHeading(fn ($record): string => 'Consultar fatura #' . $this->getRecordTitle($record));
        $this->modalDescription('Você pode pagar via PIX QrCode ou link, também informar um depósito');

        $this->modalSubmitAction(function () {
            return static::makeModalAction('submit')
                ->label('CONFIRMAR PAGAMENTO')
                ->disabled(fn () => $this->record->status !== SubscriptionInvoiceStatus::CREATED)
                ->extraAttributes([
                    'style' => 'width:100%;',
                ])
                ->submit($this->getLivewireCallMountedActionName())
                ->color(match ($color = $this->getColor()) {
                    'gray' => 'primary',
                    default => $color,
                });
        });

        $this->modalCancelAction(false);

        $this->modalIcon('heroicon-m-banknotes');

        $this->modalWidth(MaxWidth::FourExtraLarge);

        $this->successNotificationTitle(__('filament-actions::edit.single.notifications.saved.title'));

        $this->defaultColor('primary');

        $this->icon(FilamentIcon::resolve('actions::pay-action') ?? 'heroicon-m-magnifying-glass');

        $this->fillForm(function (Model $record, Table $table): array {
            $translatableContentDriver = $table->makeTranslatableContentDriver();

            if ($translatableContentDriver) {
                $data = $translatableContentDriver->getRecordAttributesToArray($record);
            } else {
                $data = $record->attributesToArray();
            }

            $relationship = $table->getRelationship();

            if ($relationship instanceof BelongsToMany) {
                $pivot = $record->getRelationValue($relationship->getPivotAccessor());

                $pivotColumns = $relationship->getPivotColumns();

                if ($translatableContentDriver) {
                    $data = [
                        ...$data,
                        ...Arr::only($translatableContentDriver->getRecordAttributesToArray($pivot), $pivotColumns),
                    ];
                } else {
                    $data = [
                        ...$data,
                        ...Arr::only($pivot->attributesToArray(), $pivotColumns),
                    ];
                }
            }

            if ($this->mutateRecordDataUsing) {
                $data = $this->evaluate($this->mutateRecordDataUsing, ['data' => $data]);
            }

            return $data;
        });

        $this->action(function (): void {
            $this->process(function (array $data, Model $record, Table $table) {

                Log::debug('Process View', [
                    'record' => $record,
                ]);

                try {

                    $record->registerPayInfo();

                } catch (\Exception $exception) {
                    $this->failureNotificationTitle($exception->getMessage())->sendFailureNotification();
                }

            });

            $this->success();
        });
    }

    public function mutateRecordDataUsing(?Closure $callback): static
    {
        $this->mutateRecordDataUsing = $callback;

        return $this;
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make(2)->schema([
                QrCodeField::make('payload.pix_link'),
                KeyValue::make('payload.bank_data')
                    ->addable(false)
                    ->valueLabel('')
                    ->hiddenLabel()
                    ->keyLabel('DADOS BANCÁRIOS')
                    ->deletable(false)
                    ->editableValues(false)
                    ->editableKeys(false),

            ]),

            CopyLinkField::make('payload.pix_link'),
        ];
    }

    public function getRecordTitle(?Model $record = null): string
    {
        return $record->invoice_id;
    }
}
