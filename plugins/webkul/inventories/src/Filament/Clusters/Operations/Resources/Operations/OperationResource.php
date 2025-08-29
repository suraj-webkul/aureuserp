<?php

namespace Webkul\Inventory\Filament\Clusters\Operations\Resources\Operations;

use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Webkul\Field\Filament\Traits\HasCustomFields;
use Webkul\Inventory\Enums;
use Webkul\Inventory\Enums\LocationType;
use Webkul\Inventory\Enums\MoveState;
use Webkul\Inventory\Enums\OperationState;
use Webkul\Inventory\Enums\ProcureMethod;
use Webkul\Inventory\Enums\ProductTracking;
use Webkul\Inventory\Facades\Inventory;
use Webkul\Inventory\Filament\Clusters\Operations\Resources\Deliveries\DeliveryResource;
use Webkul\Inventory\Filament\Clusters\Operations\Resources\Internals\InternalResource;
use Webkul\Inventory\Filament\Clusters\Operations\Resources\Operations\Schemas\OperationForm;
use Webkul\Inventory\Filament\Clusters\Operations\Resources\Operations\Schemas\OperationInfolist;
use Webkul\Inventory\Filament\Clusters\Operations\Resources\Operations\Tables\OperationsTable;
use Webkul\Inventory\Filament\Clusters\Operations\Resources\Receipts\ReceiptResource;
use Webkul\Inventory\Filament\Clusters\Products\Resources\Lots\LotResource;
use Webkul\Inventory\Filament\Clusters\Products\Resources\Packages\PackageResource;
use Webkul\Inventory\Models\Move;
use Webkul\Inventory\Models\Operation;
use Webkul\Inventory\Models\Packaging;
use Webkul\Inventory\Models\Product;
use Webkul\Inventory\Models\ProductQuantity;
use Webkul\Inventory\Settings\OperationSettings;
use Webkul\Inventory\Settings\ProductSettings;
use Webkul\Inventory\Settings\TraceabilitySettings;
use Webkul\Inventory\Settings\WarehouseSettings;
use Webkul\Product\Enums\ProductType;
use Webkul\Support\Models\UOM;
use Webkul\TableViews\Filament\Components\PresetView;

class OperationResource extends Resource
{
    use HasCustomFields;

    protected static ?string $model = Operation::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return OperationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OperationsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OperationInfolist::configure($schema);
    }

    /**
     * @param  array<mixed>  $parameters
     */
    public static function getUrl(?string $name = null, array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false): string
    {
        return match ($parameters['record']?->operationType->type) {
            Enums\OperationType::INCOMING => ReceiptResource::getUrl('view', $parameters, $isAbsolute, $panel, $tenant),
            Enums\OperationType::INTERNAL => InternalResource::getUrl('view', $parameters, $isAbsolute, $panel, $tenant),
            Enums\OperationType::OUTGOING => DeliveryResource::getUrl('view', $parameters, $isAbsolute, $panel, $tenant),
            default                       => parent::getUrl('view', $parameters, $isAbsolute, $panel, $tenant),
        };
    }

    public static function getMovesRepeater(): Repeater
    {
        return Repeater::make('moves')
            ->hiddenLabel()
            ->relationship()
            ->schema([
                Select::make('product_id')
                    ->label(__('inventories::filament/clusters/operations/resources/operation.form.tabs.operations.fields.product'))
                    ->relationship('product', 'name')
                    ->relationship(
                        'product',
                        'name',
                        fn ($query) => $query->where('type', ProductType::GOODS)->whereNull('is_configurable'),
                    )
                    ->required()
                    ->searchable()
                    ->preload()
                    ->distinct()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        static::afterProductUpdated($set, $get);
                    })
                    ->disabled(fn (Move $move): bool => $move->id && $move->state !== MoveState::DRAFT),
                Select::make('final_location_id')
                    ->label(__('inventories::filament/clusters/operations/resources/operation.form.tabs.operations.fields.final-location'))
                    ->relationship(
                        'finalLocation',
                        'full_name',
                        modifyQueryUsing: fn (Builder $query) => $query->withTrashed(),
                    )
                    ->getOptionLabelFromRecordUsing(function ($record): string {
                        return $record->full_name.($record->trashed() ? ' (Deleted)' : '');
                    })
                    ->disableOptionWhen(function ($label) {
                        return str_contains($label, ' (Deleted)');
                    })
                    ->searchable()
                    ->preload()
                    ->visible(fn (WarehouseSettings $settings) => $settings->enable_locations)
                    ->disabled(fn ($record): bool => in_array($record?->state, [MoveState::DONE, MoveState::CANCELED])),
                TextInput::make('description_picking')
                    ->label(__('inventories::filament/clusters/operations/resources/operation.form.tabs.operations.fields.description'))
                    ->maxLength(255)
                    ->disabled(fn ($record): bool => in_array($record?->state, [MoveState::DONE, MoveState::CANCELED])),
                DateTimePicker::make('scheduled_at')
                    ->label(__('inventories::filament/clusters/operations/resources/operation.form.tabs.operations.fields.scheduled-at'))
                    ->default(now())
                    ->native(false)
                    ->disabled(fn ($record): bool => in_array($record?->state, [MoveState::DONE, MoveState::CANCELED])),
                DateTimePicker::make('deadline')
                    ->label(__('inventories::filament/clusters/operations/resources/operation.form.tabs.operations.fields.deadline'))
                    ->native(false)
                    ->disabled(fn ($record): bool => in_array($record?->state, [MoveState::DONE, MoveState::CANCELED])),
                Select::make('product_packaging_id')
                    ->label(__('inventories::filament/clusters/operations/resources/operation.form.tabs.operations.fields.packaging'))
                    ->relationship('productPackaging', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn (ProductSettings $settings) => $settings->enable_packagings)
                    ->disabled(fn ($record): bool => in_array($record?->state, [MoveState::DONE, MoveState::CANCELED])),
                TextInput::make('product_uom_qty')
                    ->label(__('inventories::filament/clusters/operations/resources/operation.form.tabs.operations.fields.demand'))
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(99999999999)
                    ->default(0)
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        static::afterProductUOMQtyUpdated($set, $get);
                    })
                    ->disabled(fn (Move $move): bool => $move->id && $move->state !== MoveState::DRAFT),
                TextInput::make('quantity')
                    ->label(__('inventories::filament/clusters/operations/resources/operation.form.tabs.operations.fields.quantity'))
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(99999999999)
                    ->default(0)
                    ->required()
                    ->visible(fn (Move $move): bool => $move->id && $move->state !== MoveState::DRAFT)
                    ->disabled(fn ($record): bool => in_array($record?->state, [MoveState::DONE, MoveState::CANCELED]))
                    ->suffixAction(fn ($record) => static::getMoveLinesAction($record)),
                Select::make('uom_id')
                    ->label(__('inventories::filament/clusters/operations/resources/operation.form.tabs.operations.fields.unit'))
                    ->relationship(
                        'uom',
                        'name',
                        fn ($query) => $query->where('category_id', 1),
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        static::afterUOMUpdated($set, $get);
                    })
                    ->visible(fn (ProductSettings $settings): bool => $settings->enable_uom)
                    ->disabled(fn ($record): bool => in_array($record?->state, [MoveState::DONE, MoveState::CANCELED])),
                Toggle::make('is_picked')
                    ->label(__('inventories::filament/clusters/operations/resources/operation.form.tabs.operations.fields.picked'))
                    ->default(0)
                    ->inline(false)
                    ->disabled(fn ($record): bool => in_array($record?->state, [MoveState::DONE, MoveState::CANCELED])),
                Hidden::make('product_qty')
                    ->default(0),
            ])
            ->columns(4)
            ->mutateRelationshipDataBeforeCreateUsing(function (array $data, $record) {
                $product = Product::find($data['product_id']);

                $data = array_merge($data, [
                    'creator_id'              => Auth::id(),
                    'company_id'              => Auth::user()->default_company_id,
                    'warehouse_id'            => $record->destinationLocation->warehouse_id,
                    'state'                   => $record->state->value,
                    'name'                    => $product->name,
                    'procure_method'          => ProcureMethod::MAKE_TO_STOCK,
                    'uom_id'                  => $data['uom_id'] ?? $product->uom_id,
                    'operation_type_id'       => $record->operation_type_id,
                    'quantity'                => null,
                    'source_location_id'      => $record->source_location_id,
                    'destination_location_id' => $record->destination_location_id,
                    'scheduled_at'            => $record->scheduled_at ?? now(),
                    'reference'               => $record->name,
                ]);

                return $data;
            })
            ->mutateRelationshipDataBeforeSaveUsing(function (array $data, $record) {
                if (isset($data['quantity'])) {
                    $record->fill([
                        'quantity' => $data['quantity'] ?? null,
                    ]);

                    Inventory::computeTransferMove($record);

                    Inventory::computeTransferState($record->operation);
                }

                return $data;
            })
            ->deletable(fn ($record): bool => ! in_array($record?->state, [OperationState::DONE, OperationState::CANCELED]))
            ->addable(fn ($record): bool => ! in_array($record?->state, [OperationState::DONE, OperationState::CANCELED]));
    }

    public static function getMoveLinesAction($move): Action
    {
        $columns = 2;

        if (
            app(TraceabilitySettings::class)->enable_lots_serial_numbers
            && (
                $move->product->tracking == ProductTracking::LOT
                || $move->product->tracking == ProductTracking::SERIAL
            )
            && $move->sourceLocation->type == LocationType::SUPPLIER
        ) {
            $columns++;
        }

        if ($move->sourceLocation->type == LocationType::INTERNAL) {
            $columns++;
        }

        if ($move->destinationLocation->type != LocationType::INTERNAL) {
            $columns--;
        }

        if (app(OperationSettings::class)->enable_packages) {
            $columns++;
        }

        return Action::make('manageLines')
            ->icon('heroicon-m-bars-4')
            ->label(__('inventories::filament/clusters/operations/resources/operation.form.tabs.operations.fields.lines.modal-heading'))
            ->modalSubmitActionLabel('Save')
            ->visible(app(WarehouseSettings::class)->enable_locations)
            ->schema([
                Repeater::make('lines')
                    ->hiddenLabel()
                    ->relationship('lines')
                    ->schema([
                        Select::make('quantity_id')
                            ->label(__(__('inventories::filament/clusters/operations/resources/operation.form.tabs.operations.fields.lines.fields.pick-from')))
                            ->options(function ($record) use ($move) {
                                if (in_array($record?->state, [MoveState::DONE, MoveState::CANCELED])) {
                                    $nameParts = array_filter([
                                        $record->sourceLocation->full_name,
                                        $record->lot?->name,
                                        $record->package?->name,
                                    ]);

                                    return [
                                        $record->id => implode(' - ', $nameParts),
                                    ];
                                }

                                return ProductQuantity::with(['location', 'lot', 'package'])
                                    ->where('product_id', $move->product_id)
                                    ->whereHas('location', function (Builder $query) use ($move) {
                                        $query->where('id', $move->source_location_id)
                                            ->orWhere('parent_id', $move->source_location_id);
                                    })
                                    ->get()
                                    ->mapWithKeys(function ($quantity) {
                                        $nameParts = array_filter([
                                            $quantity->location->full_name,
                                            $quantity->lot?->name,
                                            $quantity->package?->name,
                                        ]);

                                        return [$quantity->id => implode(' - ', $nameParts)];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateHydrated(function (Select $component, $record) {
                                if (in_array($record?->state, [MoveState::DONE, MoveState::CANCELED])) {
                                    $component->state($record->id);

                                    return;
                                }

                                $productQuantity = ProductQuantity::with(['location', 'lot', 'package'])
                                    ->where('product_id', $record?->product_id)
                                    ->where('location_id', $record?->source_location_id)
                                    ->where('lot_id', $record?->lot_id ?? null)
                                    ->where('package_id', $record?->package_id ?? null)
                                    ->first();

                                $component->state($productQuantity?->id);
                            })
                            ->afterStateUpdated(function (Set $set, Get $get) use ($move) {
                                $productQuantity = ProductQuantity::find($get('quantity_id'));

                                $set('lot_id', $productQuantity?->lot_id);

                                $set('result_package_id', $productQuantity?->package_id);

                                if ($productQuantity?->quantity) {
                                    if (! $move->uom_id) {
                                        $set('qty', $productQuantity->quantity);
                                    } else {
                                        $set('qty', (float) ($productQuantity->quantity ?? 0) * $move->uom->factor);
                                    }
                                }
                            })
                            ->visible($move->sourceLocation->type == LocationType::INTERNAL)
                            ->disabled(fn (): bool => in_array($move->state, [MoveState::DONE, MoveState::CANCELED])),
                        Select::make('lot_id')
                            ->label(__('inventories::filament/clusters/operations/resources/operation.form.tabs.operations.fields.lines.fields.lot'))
                            ->relationship(
                                name: 'lot',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query) => $query->where('product_id', $move->product_id),
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn (): bool => in_array($move->state, [MoveState::DONE, MoveState::CANCELED]))
                            ->disableOptionWhen(fn () => ! $move->operationType->use_existing_lots)
                            ->createOptionForm(fn (Schema $schema): Schema => LotResource::form($schema))
                            ->createOptionAction(function (Action $action) use ($move) {
                                $action->visible($move->operationType->use_create_lots)
                                    ->mutateDataUsing(function (array $data) use ($move) {
                                        $data['product_id'] = $move->product_id;

                                        return $data;
                                    });
                            })
                            ->visible(
                                fn (TraceabilitySettings $settings): bool => $settings->enable_lots_serial_numbers
                                    && (
                                        $move->product->tracking == ProductTracking::LOT
                                        || $move->product->tracking == ProductTracking::SERIAL
                                    )
                                    && $move->sourceLocation->type == LocationType::SUPPLIER
                            ),
                        Select::make('destination_location_id')
                            ->label(__('inventories::filament/clusters/operations/resources/operation.form.tabs.operations.fields.lines.fields.location'))
                            ->relationship(
                                name: 'destinationLocation',
                                titleAttribute: 'full_name',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->withTrashed()
                                    ->where('type', '<>', LocationType::VIEW)
                                    ->where(function ($query) use ($move) {
                                        $query->where('id', $move->destination_location_id)
                                            ->orWhere('parent_id', $move->destination_location_id);
                                    })
                            )
                            ->getOptionLabelFromRecordUsing(function ($record): string {
                                return $record->full_name.($record->trashed() ? ' (Deleted)' : '');
                            })
                            ->disableOptionWhen(function ($label) {
                                return str_contains($label, ' (Deleted)');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->default($move->destination_location_id)
                            ->afterStateUpdated(function (Set $set) {
                                $set('result_package_id', null);
                            })
                            ->visible($move->destinationLocation->type == LocationType::INTERNAL)
                            ->disabled(fn (): bool => in_array($move->state, [MoveState::DONE, MoveState::CANCELED])),
                        Select::make('result_package_id')
                            ->label(__('inventories::filament/clusters/operations/resources/operation.form.tabs.operations.fields.lines.fields.package'))
                            ->relationship(
                                name: 'resultPackage',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query, Get $get, $record) => $query
                                    ->where(function ($query) use ($get, $record) {
                                        $query->where('location_id', $get('destination_location_id'))
                                            ->orWhere('id', $record?->result_package_id ?? $get('result_package_id'))
                                            ->orWhereNull('location_id');
                                    }),
                            )
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema): Schema => PackageResource::form($schema))
                            ->createOptionAction(function (Action $action) use ($move) {
                                $action->mutateDataUsing(function (array $data) use ($move) {
                                    $data['company_id'] = $move->company_id;

                                    return $data;
                                });
                            })
                            ->disabled(fn (): bool => in_array($move->state, [MoveState::DONE, MoveState::CANCELED]))
                            ->visible(fn (OperationSettings $settings) => $settings->enable_packages),
                        TextInput::make('qty')
                            ->label(__('inventories::filament/clusters/operations/resources/operation.form.tabs.operations.fields.lines.fields.quantity'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(99999999999)
                            ->maxValue(fn () => $move->product->tracking == ProductTracking::SERIAL ? 1 : 999999999)
                            ->required()
                            ->suffix(function () use ($move) {
                                if (! app(ProductSettings::class)->enable_uom) {
                                    return false;
                                }

                                return $move->uom->name;
                            })
                            ->disabled(fn (): bool => in_array($move->state, [MoveState::DONE, MoveState::CANCELED])),
                    ])
                    ->defaultItems(0)
                    ->addActionLabel(__('inventories::filament/clusters/operations/resources/operation.form.tabs.operations.fields.lines.add-line'))
                    ->columns($columns)
                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data, Move $move): array {
                        if (isset($data['quantity_id'])) {
                            $productQuantity = ProductQuantity::find($data['quantity_id']);

                            $data['lot_id'] = $productQuantity?->lot_id;

                            $data['package_id'] = $productQuantity?->package_id;
                        }

                        $data['reference'] = $move->reference;
                        $data['state'] = $move->state;
                        $data['uom_qty'] = static::calculateProductQuantity($data['uom_id'] ?? $move->uom_id, $data['qty']);
                        $data['scheduled_at'] = $move->scheduled_at;
                        $data['operation_id'] = $move->operation_id;
                        $data['move_id'] = $move->id;
                        $data['source_location_id'] = $move->source_location_id;
                        $data['uom_id'] ??= $move->uom_id;
                        $data['creator_id'] = Auth::id();
                        $data['product_id'] = $move->product_id;
                        $data['company_id'] = $move->company_id;

                        return $data;
                    })
                    ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                        if (isset($data['quantity_id'])) {
                            $productQuantity = ProductQuantity::find($data['quantity_id']);

                            $data['lot_id'] = $productQuantity?->lot_id;

                            $data['package_id'] = $productQuantity?->package_id;
                        }

                        return $data;
                    })
                    ->deletable(fn (): bool => ! in_array($move->state, [MoveState::DONE, MoveState::CANCELED]))
                    ->addable(fn (): bool => ! in_array($move->state, [MoveState::DONE, MoveState::CANCELED])),
            ])
            ->modalWidth('6xl')
            ->mountUsing(function (Schema $schema, Move $record) {
                $schema->fill([]);
            })
            ->modalSubmitAction(
                fn ($action, Move $record) => $action
                    ->visible(! in_array($move->state, [MoveState::DONE, MoveState::CANCELED]))
            )
            ->action(function (Set $set, array $data, Move $record): void {
                $totalQty = $record->lines()->sum('qty');

                $record->fill([
                    'quantity' => $totalQty,
                ]);

                Inventory::computeTransferMove($record);

                $set('quantity', $totalQty);
            });
    }

    public static function getPresetTableViews(): array
    {
        return [
            'todo_receipts' => PresetView::make(__('inventories::filament/clusters/operations/resources/operation.tabs.todo'))
                ->favorite()
                ->icon('heroicon-s-clipboard-document-list')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotIn('state', [OperationState::DONE, OperationState::CANCELED])),
            'my_receipts' => PresetView::make(__('inventories::filament/clusters/operations/resources/operation.tabs.my'))
                ->favorite()
                ->icon('heroicon-s-user')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('user_id', Auth::id())),
            'favorite_receipts' => PresetView::make(__('inventories::filament/clusters/operations/resources/operation.tabs.starred'))
                ->favorite()
                ->icon('heroicon-s-star')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_favorite', true)),
            'draft_receipts' => PresetView::make(__('inventories::filament/clusters/operations/resources/operation.tabs.draft'))
                ->favorite()
                ->icon('heroicon-s-pencil-square')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('state', OperationState::DRAFT)),
            'waiting_receipts' => PresetView::make(__('inventories::filament/clusters/operations/resources/operation.tabs.waiting'))
                ->favorite()
                ->icon('heroicon-s-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('state', OperationState::CONFIRMED)),
            'ready_receipts' => PresetView::make(__('inventories::filament/clusters/operations/resources/operation.tabs.ready'))
                ->favorite()
                ->icon('heroicon-s-play-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('state', OperationState::ASSIGNED)),
            'done_receipts' => PresetView::make(__('inventories::filament/clusters/operations/resources/operation.tabs.done'))
                ->favorite()
                ->icon('heroicon-s-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('state', OperationState::DONE)),
            'canceled_receipts' => PresetView::make(__('inventories::filament/clusters/operations/resources/operation.tabs.canceled'))
                ->icon('heroicon-s-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('state', OperationState::CANCELED)),
        ];
    }

    private static function afterProductUpdated(Set $set, Get $get): void
    {
        if (! $get('product_id')) {
            return;
        }

        $product = Product::find($get('product_id'));

        $set('uom_id', $product->uom_id);

        $productQuantity = static::calculateProductQuantity($get('uom_id'), $get('product_uom_qty'));

        $set('product_qty', round($productQuantity, 2));

        $packaging = static::getBestPackaging($get('product_id'), round($productQuantity, 2));

        $set('product_packaging_id', $packaging['packaging_id'] ?? null);
    }

    private static function afterProductUOMQtyUpdated(Set $set, Get $get): void
    {
        if (! $get('product_id')) {
            return;
        }

        $productQuantity = static::calculateProductQuantity($get('uom_id'), $get('product_uom_qty'));

        $set('product_qty', round($productQuantity, 2));

        $packaging = static::getBestPackaging($get('product_id'), $productQuantity);

        $set('product_packaging_id', $packaging['packaging_id'] ?? null);
    }

    private static function afterUOMUpdated(Set $set, Get $get): void
    {
        if (! $get('product_id')) {
            return;
        }

        $productQuantity = static::calculateProductQuantity($get('uom_id'), $get('product_uom_qty'));

        $set('product_qty', round($productQuantity, 2));

        $packaging = static::getBestPackaging($get('product_id'), $productQuantity);

        $set('product_packaging_id', $packaging['packaging_id'] ?? null);
    }

    public static function calculateProductQuantity($uomId, $uomQuantity)
    {
        if (! $uomId) {
            return self::normalizeZero((float) ($uomQuantity ?? 0));
        }

        $uom = Uom::find($uomId);

        if (! $uom || ! is_numeric($uom->factor) || $uom->factor == 0) {
            return 0;
        }

        $quantity = (float) ($uomQuantity ?? 0) / $uom->factor;

        return self::normalizeZero($quantity);
    }

    protected static function normalizeZero(float $value): float
    {
        return $value == 0 ? 0.0 : $value; // convert -0.0 to 0.0
    }

    private static function getBestPackaging($productId, $quantity)
    {
        $product = Product::find($productId);

        $packagings = Packaging::where('product_id', $productId)
            ->orderByDesc('qty')
            ->get();

        foreach ($packagings as $packaging) {
            if ($quantity && $quantity % $packaging->qty == 0) {
                return [
                    'packaging_id'  => $packaging->id,
                    'packaging_qty' => round($quantity / $packaging->qty, 2),
                ];
            }
        }

        return null;
    }
}
