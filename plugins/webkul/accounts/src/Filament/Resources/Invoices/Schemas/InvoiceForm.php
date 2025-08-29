<?php

namespace Webkul\Account\Filament\Resources\Invoices\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Webkul\Account\Enums\MoveState;
use Webkul\Account\Enums\PaymentState;
use Webkul\Account\Filament\Resources\BankAccounts\BankAccountResource;
use Webkul\Account\Filament\Resources\Invoices\InvoiceResource;
use Webkul\Account\Livewire\InvoiceSummary;
use Webkul\Field\Filament\Forms\Components\ProgressStepper;
use Webkul\Support\Models\Currency;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ProgressStepper::make('state')
                    ->hiddenLabel()
                    ->inline()
                    ->options(function ($record) {
                        $options = MoveState::options();

                        if (
                            $record
                            && $record->state != MoveState::CANCEL->value
                        ) {
                            unset($options[MoveState::CANCEL->value]);
                        }

                        if ($record == null) {
                            unset($options[MoveState::CANCEL->value]);
                        }

                        return $options;
                    })
                    ->default(MoveState::DRAFT->value)
                    ->columnSpan('full')
                    ->disabled()
                    ->live()
                    ->reactive(),
                Section::make(__('accounts::filament/resources/invoice.form.section.general.title'))
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Actions::make([
                            Action::make('payment_state')
                                ->icon(fn($record) => $record->payment_state->getIcon())
                                ->color(fn($record) => $record->payment_state->getColor())
                                ->visible(fn($record) => $record && in_array($record->payment_state, [PaymentState::PAID, PaymentState::REVERSED]))
                                ->label(fn($record) => $record->payment_state->getLabel())
                                ->size(Size::ExtraLarge->value),
                        ]),
                        Group::make()
                            ->schema([
                                Group::make()
                                    ->schema([
                                        Select::make('partner_id')
                                            ->label(__('accounts::filament/resources/invoice.form.section.general.fields.customer'))
                                            ->relationship(
                                                'partner',
                                                'name',
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->disabled(fn($record) => $record && in_array($record->state, [MoveState::POSTED, MoveState::CANCEL])),
                                    ]),
                                DatePicker::make('invoice_date')
                                    ->label(__('accounts::filament/resources/invoice.form.section.general.fields.invoice-date'))
                                    ->default(now())
                                    ->native(false)
                                    ->disabled(fn($record) => $record && in_array($record->state, [MoveState::POSTED, MoveState::CANCEL])),
                                DatePicker::make('invoice_date_due')
                                    ->required()
                                    ->default(now())
                                    ->native(false)
                                    ->live()
                                    ->hidden(fn(Get $get) => $get('invoice_payment_term_id') !== null)
                                    ->label(__('accounts::filament/resources/invoice.form.section.general.fields.due-date')),
                                Select::make('invoice_payment_term_id')
                                    ->relationship(
                                        'invoicePaymentTerm',
                                        'name',
                                        modifyQueryUsing: fn(Builder $query) => $query->withTrashed(),
                                    )
                                    ->getOptionLabelFromRecordUsing(function ($record): string {
                                        return $record->name . ($record->trashed() ? ' (Deleted)' : '');
                                    })
                                    ->disableOptionWhen(function ($label) {
                                        return str_contains($label, ' (Deleted)');
                                    })
                                    ->required(fn(Get $get) => $get('invoice_date_due') === null)
                                    ->live()
                                    ->searchable()
                                    ->preload()
                                    ->label(__('accounts::filament/resources/invoice.form.section.general.fields.payment-term')),
                            ])->columns(2),
                    ]),
                Tabs::make()
                    ->schema([
                        Tab::make(__('accounts::filament/resources/invoice.form.tabs.invoice-lines.title'))
                            ->icon('heroicon-o-list-bullet')
                            ->schema([
                                InvoiceResource::getProductRepeater(),
                                Livewire::make(InvoiceSummary::class, function (Get $get) {
                                    return [
                                        'currency' => Currency::find($get('currency_id')),
                                        'products' => $get('products'),
                                    ];
                                })
                                    ->live()
                                    ->reactive(),
                            ]),
                        Tab::make(__('accounts::filament/resources/invoice.form.tabs.other-information.title'))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Fieldset::make(__('accounts::filament/resources/invoice.form.tabs.other-information.fieldset.invoice.title'))
                                    ->schema([
                                        TextInput::make('reference')
                                            ->label(__('accounts::filament/resources/invoice.form.tabs.other-information.fieldset.invoice.fields.customer-reference'))
                                            ->maxLength(255),
                                        Select::make('invoice_user_id')
                                            ->relationship('invoiceUser', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->label(__('accounts::filament/resources/invoice.form.tabs.other-information.fieldset.invoice.fields.sales-person')),
                                        Select::make('partner_bank_id')
                                            ->relationship('partnerBank', 'account_number')
                                            ->searchable()
                                            ->preload()
                                            ->label(__('accounts::filament/resources/invoice.form.tabs.other-information.fieldset.invoice.fields.recipient-bank'))
                                            ->createOptionForm(fn($form) => BankAccountResource::form($form))
                                            ->disabled(fn($record) => $record && in_array($record->state, [MoveState::POSTED, MoveState::CANCEL])),
                                        TextInput::make('payment_reference')
                                            ->label(__('accounts::filament/resources/invoice.form.tabs.other-information.fieldset.invoice.fields.payment-reference')),
                                        DatePicker::make('delivery_date')
                                            ->native(false)
                                            ->label(__('accounts::filament/resources/invoice.form.tabs.other-information.fieldset.invoice.fields.delivery-date'))
                                            ->disabled(fn($record) => $record && in_array($record->state, [MoveState::POSTED, MoveState::CANCEL])),
                                    ]),
                                Fieldset::make(__('accounts::filament/resources/invoice.form.tabs.other-information.fieldset.accounting.title'))
                                    ->schema([
                                        Select::make('invoice_incoterm_id')
                                            ->relationship('invoiceIncoterm', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->label(__('accounts::filament/resources/invoice.form.tabs.other-information.fieldset.accounting.fieldset.incoterm')),
                                        TextInput::make('incoterm_location')
                                            ->label(__('accounts::filament/resources/invoice.form.tabs.other-information.fieldset.accounting.fieldset.incoterm-location')),
                                        Select::make('preferred_payment_method_line_id')
                                            ->relationship('paymentMethodLine', 'name')
                                            ->preload()
                                            ->searchable()
                                            ->label(__('accounts::filament/resources/invoice.form.tabs.other-information.fieldset.accounting.fieldset.payment-method')),
                                        Toggle::make('auto_post')
                                            ->default(0)
                                            ->inline(false)
                                            ->label(__('accounts::filament/resources/invoice.form.tabs.other-information.fieldset.accounting.fieldset.auto-post'))
                                            ->disabled(fn($record) => $record && in_array($record->state, [MoveState::POSTED, MoveState::CANCEL])),
                                        Toggle::make('checked')
                                            ->inline(false)
                                            ->label(__('accounts::filament/resources/invoice.form.tabs.other-information.fieldset.accounting.fieldset.checked')),
                                    ]),
                                Fieldset::make(__('accounts::filament/resources/invoice.form.tabs.other-information.fieldset.additional-information.title'))
                                    ->schema([
                                        Select::make('company_id')
                                            ->label(__('accounts::filament/resources/invoice.form.tabs.other-information.fieldset.additional-information.fields.company'))
                                            ->relationship('company', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->default(Auth::user()->default_company_id),
                                        Select::make('currency_id')
                                            ->label(__('accounts::filament/resources/invoice.form.tabs.other-information.fieldset.additional-information.fields.currency'))
                                            ->relationship('currency', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->reactive()
                                            ->default(Auth::user()->defaultCompany?->currency_id),
                                    ]),
                                Fieldset::make(__('accounts::filament/resources/invoice.form.tabs.other-information.fieldset.marketing.title'))
                                    ->schema([
                                        Select::make('campaign_id')
                                            ->relationship('campaign', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->label(__('accounts::filament/resources/invoice.form.tabs.other-information.fieldset.marketing.fields.campaign')),
                                        Select::make('medium_id')
                                            ->relationship('medium', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->label(__('accounts::filament/resources/invoice.form.tabs.other-information.fieldset.marketing.fields.medium')),
                                        Select::make('source_id')
                                            ->relationship('source', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->label(__('accounts::filament/resources/invoice.form.tabs.other-information.fieldset.marketing.fields.source')),
                                    ]),
                            ]),
                        Tab::make(__('accounts::filament/resources/invoice.form.tabs.term-and-conditions.title'))
                            ->icon('heroicon-o-clipboard-document-list')
                            ->schema([
                                RichEditor::make('narration')
                                    ->hiddenLabel(),
                            ]),
                    ]),
            ])
            ->columns(1);
    }
}
