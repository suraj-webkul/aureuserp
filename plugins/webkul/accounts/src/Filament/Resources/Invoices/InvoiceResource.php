<?php

namespace Webkul\Account\Filament\Resources\Invoices;

use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Webkul\Account\Enums\MoveState;
use Webkul\Account\Enums\TypeTaxUse;
use Webkul\Account\Facades\Tax;
use Webkul\Account\Filament\Resources\Invoices\Pages\CreateInvoice;
use Webkul\Account\Filament\Resources\Invoices\Pages\EditInvoice;
use Webkul\Account\Filament\Resources\Invoices\Pages\ListInvoices;
use Webkul\Account\Filament\Resources\Invoices\Pages\ViewInvoice;
use Webkul\Account\Filament\Resources\Invoices\Schemas\InvoiceForm;
use Webkul\Account\Filament\Resources\Invoices\Schemas\InvoiceInfolist;
use Webkul\Account\Filament\Resources\Invoices\Tables\InvoicesTable;
use Webkul\Account\Models\Move as AccountMove;
use Webkul\Invoice\Models\Product;
use Webkul\Invoice\Settings\ProductSettings;
use Webkul\Support\Models\UOM;
use BackedEnum;

class InvoiceResource extends Resource
{
    protected static ?string $model = AccountMove::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static bool $shouldRegisterNavigation = false;

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            __('accounts::filament/resources/invoice.global-search.number') => $record?->name ?? '—',
            __('accounts::filament/resources/invoice.global-search.customer') => $record?->invoice_partner_display_name ?? '—',
            __('accounts::filament/resources/invoice.global-search.invoice-date') => $record?->invoice_date ?? '—',
            __('accounts::filament/resources/invoice.global-search.invoice-date-due') => $record?->invoice_date_due ?? '—',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return InvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvoicesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InvoiceInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvoices::route('/'),
            'create' => CreateInvoice::route('/create'),
            'view' => ViewInvoice::route('/{record}'),
            'edit' => EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function getProductRepeater(): Repeater
    {
        return Repeater::make('products')
            ->relationship('lines')
            ->hiddenLabel()
            ->live()
            ->reactive()
            ->label(__('accounts::filament/resources/invoice.form.tabs.invoice-lines.repeater.products.title'))
            ->addActionLabel(__('accounts::filament/resources/invoice.form.tabs.invoice-lines.repeater.products.add-product'))
            ->collapsible()
            ->defaultItems(0)
            ->itemLabel(function ($state) {
                if (!empty($state['name'])) {
                    return $state['name'];
                }

                $product = Product::find($state['product_id']);

                return $product->name ?? null;
            })
            ->deleteAction(fn(Action $action) => $action->requiresConfirmation())
            ->schema([
                Group::make()
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                Select::make('product_id')
                                    ->label(__('accounts::filament/resources/invoice.form.tabs.invoice-lines.repeater.products.fields.product'))
                                    ->relationship(
                                        'product',
                                        'name',
                                        fn($query) => $query->where('is_configurable', null),
                                    )
                                    ->getOptionLabelUsing(function ($record) {
                                        if ($record->product) {
                                            return $record->product->name;
                                        }

                                        return $record->name;
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->dehydrated()
                                    ->disabled(fn($record) => $record && in_array($record->parent_state, [MoveState::POSTED, MoveState::CANCEL]))
                                    ->afterStateUpdated(fn(Set $set, Get $get) => static::afterProductUpdated($set, $get))
                                    ->required(),
                                TextInput::make('quantity')
                                    ->label(__('accounts::filament/resources/invoice.form.tabs.invoice-lines.repeater.products.fields.quantity'))
                                    ->required()
                                    ->default(1)
                                    ->numeric()
                                    ->maxValue(99999999999)
                                    ->live()
                                    ->dehydrated()
                                    ->disabled(fn($record) => $record && in_array($record->parent_state, [MoveState::POSTED, MoveState::CANCEL]))
                                    ->afterStateUpdated(fn(Set $set, Get $get) => static::afterProductQtyUpdated($set, $get)),
                                Select::make('uom_id')
                                    ->label(__('accounts::filament/resources/invoice.form.tabs.invoice-lines.repeater.products.fields.unit'))
                                    ->relationship(
                                        'uom',
                                        'name',
                                        fn($query) => $query->where('category_id', 1)->orderBy('id'),
                                    )
                                    ->required()
                                    ->live()
                                    ->selectablePlaceholder(false)
                                    ->dehydrated()
                                    ->disabled(fn($record) => $record && in_array($record->parent_state, [MoveState::POSTED, MoveState::CANCEL]))
                                    ->afterStateUpdated(fn(Set $set, Get $get) => static::afterUOMUpdated($set, $get))
                                    ->visible(fn(ProductSettings $settings) => $settings->enable_uom),
                                Select::make('taxes')
                                    ->label(__('accounts::filament/resources/invoice.form.tabs.invoice-lines.repeater.products.fields.taxes'))
                                    ->relationship(
                                        'taxes',
                                        'name',
                                        function (Builder $query) {
                                            return $query->where('type_tax_use', TypeTaxUse::SALE->value);
                                        },
                                    )
                                    ->searchable()
                                    ->multiple()
                                    ->preload()
                                    ->dehydrated()
                                    ->disabled(fn($record) => $record && in_array($record->parent_state, [MoveState::POSTED, MoveState::CANCEL]))
                                    ->afterStateHydrated(fn(Get $get, Set $set) => self::calculateLineTotals($set, $get))
                                    ->afterStateUpdated(fn(Get $get, Set $set, $state) => self::calculateLineTotals($set, $get))
                                    ->live(),
                                TextInput::make('discount')
                                    ->label(__('Discount Percentage'))
                                    ->label(__('accounts::filament/resources/invoice.form.tabs.invoice-lines.repeater.products.fields.discount-percentage'))
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->maxValue(99999999999)
                                    ->live()
                                    ->dehydrated()
                                    ->disabled(fn($record) => $record && in_array($record->parent_state, [MoveState::POSTED, MoveState::CANCEL]))
                                    ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateLineTotals($set, $get)),
                                TextInput::make('price_unit')
                                    ->label(__('accounts::filament/resources/invoice.form.tabs.invoice-lines.repeater.products.fields.unit-price'))
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->maxValue(99999999999)
                                    ->required()
                                    ->live()
                                    ->dehydrated()
                                    ->disabled(fn($record) => $record && in_array($record->parent_state, [MoveState::POSTED, MoveState::CANCEL]))
                                    ->afterStateUpdated(fn(Set $set, Get $get) => self::calculateLineTotals($set, $get)),
                                TextInput::make('price_subtotal')
                                    ->label(__('accounts::filament/resources/invoice.form.tabs.invoice-lines.repeater.products.fields.sub-total'))
                                    ->default(0)
                                    ->dehydrated()
                                    ->disabled(fn($record) => $record && in_array($record->parent_state, [MoveState::POSTED, MoveState::CANCEL])),
                                Hidden::make('product_uom_qty')
                                    ->default(0),
                                Hidden::make('price_tax')
                                    ->default(0),
                                Hidden::make('price_total')
                                    ->default(0),
                            ]),
                    ])
                    ->columns(2),
            ])
            ->mutateRelationshipDataBeforeCreateUsing(fn(array $data, $record) => static::mutateProductRelationship($data, $record))
            ->mutateRelationshipDataBeforeSaveUsing(fn(array $data, $record) => static::mutateProductRelationship($data, $record));
    }

    public static function mutateProductRelationship(array $data, $record): array
    {
        $data['currency_id'] = $record->currency_id;

        return $data;
    }

    private static function afterProductUpdated(Set $set, Get $get): void
    {
        if (!$get('product_id')) {
            return;
        }

        $product = Product::find($get('product_id'));

        $set('uom_id', $product->uom_id);

        $priceUnit = static::calculateUnitPrice($get('uom_id'), $product->price ?? $product->cost);

        $set('price_unit', round($priceUnit, 2));

        $set('taxes', $product->productTaxes->pluck('id')->toArray());

        $uomQuantity = static::calculateUnitQuantity($get('uom_id'), $get('quantity'));

        $set('product_uom_qty', round($uomQuantity, 2));

        self::calculateLineTotals($set, $get);
    }

    private static function afterProductQtyUpdated(Set $set, Get $get): void
    {
        if (!$get('product_id')) {
            return;
        }

        $uomQuantity = static::calculateUnitQuantity($get('uom_id'), $get('quantity'));

        $set('product_uom_qty', round($uomQuantity, 2));

        self::calculateLineTotals($set, $get);
    }

    private static function afterUOMUpdated(Set $set, Get $get): void
    {
        if (!$get('product_id')) {
            return;
        }

        $uomQuantity = static::calculateUnitQuantity($get('uom_id'), $get('quantity'));

        $set('product_uom_qty', round($uomQuantity, 2));

        $product = Product::find($get('product_id'));

        $priceUnit = static::calculateUnitPrice($get('uom_id'), $product->cost ?? $product->price);

        $set('price_unit', round($priceUnit, 2));

        self::calculateLineTotals($set, $get);
    }

    private static function calculateUnitQuantity($uomId, $quantity)
    {
        if (!$uomId) {
            return $quantity;
        }

        $uom = Uom::find($uomId);

        return (float) ($quantity ?? 0) / $uom->factor;
    }

    private static function calculateUnitPrice($uomId, $price)
    {
        if (!$uomId) {
            return $price;
        }

        $uom = Uom::find($uomId);

        return (float) ($price / $uom->factor);
    }

    private static function calculateLineTotals(Set $set, Get $get): void
    {
        if (!$get('product_id')) {
            $set('price_unit', 0);

            $set('discount', 0);

            $set('price_tax', 0);

            $set('price_subtotal', 0);

            $set('price_total', 0);

            return;
        }

        $priceUnit = floatval($get('price_unit'));

        $quantity = floatval($get('quantity') ?? 1);

        $subTotal = $priceUnit * $quantity;

        $discountValue = floatval($get('discount') ?? 0);

        if ($discountValue > 0) {
            $discountAmount = $subTotal * ($discountValue / 100);

            $subTotal = $subTotal - $discountAmount;
        }

        $taxIds = $get('taxes') ?? [];

        [$subTotal, $taxAmount] = Tax::collect($taxIds, $subTotal, $quantity);

        $set('price_subtotal', round($subTotal, 4));

        $set('price_tax', $taxAmount);

        $set('price_total', $subTotal + $taxAmount);
    }
}
