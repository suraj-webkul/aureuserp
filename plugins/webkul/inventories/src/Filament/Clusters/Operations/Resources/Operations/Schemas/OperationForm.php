<?php

namespace Webkul\Inventory\Filament\Clusters\Operations\Resources\Operations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Webkul\Field\Filament\Forms\Components\ProgressStepper;
use Webkul\Inventory\Enums;
use Webkul\Inventory\Enums\MoveType;
use Webkul\Inventory\Enums\OperationState;
use Webkul\Inventory\Filament\Clusters\Operations\Resources\Operations\OperationResource;
use Webkul\Inventory\Models\OperationType;
use Webkul\Inventory\Settings\WarehouseSettings;
use Webkul\Partner\Filament\Resources\Partners\PartnerResource;

class OperationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ProgressStepper::make('state')
                    ->hiddenLabel()
                    ->inline()
                    ->options(OperationState::options())
                    ->options(function ($record) {
                        $options = OperationState::options();

                        if ($record && $record->state !== OperationState::CANCELED) {
                            unset($options[OperationState::CANCELED->value]);
                        }

                        return $options;
                    })
                    ->default(OperationState::DRAFT)
                    ->disabled(),
                Section::make(__('inventories::filament/clusters/operations/resources/operation.form.sections.general.title'))
                    ->schema([
                        Select::make('partner_id')
                            ->label(__('inventories::filament/clusters/operations/resources/operation.form.sections.general.fields.receive-from'))
                            ->relationship('partner', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema): Schema => PartnerResource::form($schema))
                            ->visible(fn (Get $get): bool => OperationType::withTrashed()->find($get('operation_type_id'))?->type == Enums\OperationType::INCOMING)
                            ->disabled(fn ($record): bool => in_array($record?->state, [OperationState::DONE, OperationState::CANCELED])),
                        Select::make('partner_id')
                            ->label(__('inventories::filament/clusters/operations/resources/operation.form.sections.general.fields.contact'))
                            ->relationship('partner', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema): Schema => PartnerResource::form($schema))
                            ->visible(fn (Get $get): bool => OperationType::withTrashed()->find($get('operation_type_id'))?->type == Enums\OperationType::INTERNAL)
                            ->disabled(fn ($record): bool => in_array($record?->state, [OperationState::DONE, OperationState::CANCELED])),
                        Select::make('partner_id')
                            ->label(__('inventories::filament/clusters/operations/resources/operation.form.sections.general.fields.delivery-address'))
                            ->relationship('partner', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn (Schema $schema): Schema => PartnerResource::form($schema))
                            ->visible(fn (Get $get): bool => OperationType::withTrashed()->find($get('operation_type_id'))?->type == Enums\OperationType::OUTGOING)
                            ->disabled(fn ($record): bool => in_array($record?->state, [OperationState::DONE, OperationState::CANCELED])),
                        Select::make('operation_type_id')
                            ->label(__('inventories::filament/clusters/operations/resources/operation.form.sections.general.fields.operation-type'))
                            ->relationship(
                                name: 'operationType',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query) => $query->withTrashed()
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->getOptionLabelFromRecordUsing(function (OperationType $record) {
                                if (! $record->warehouse) {
                                    return $record->name;
                                }

                                return $record->warehouse->name.': '.$record->name.($record->trashed() ? ' (Deleted)' : '');
                            })
                            ->disableOptionWhen(function ($label) {
                                return str_contains($label, ' (Deleted)');
                            })
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                $operationType = OperationType::withTrashed()->find($get('operation_type_id'));

                                $set('source_location_id', $operationType?->source_location_id);
                                $set('destination_location_id', $operationType?->destination_location_id);
                            })
                            ->disabled(fn ($record): bool => in_array($record?->state, [OperationState::DONE, OperationState::CANCELED])),
                        Select::make('source_location_id')
                            ->label(__('inventories::filament/clusters/operations/resources/operation.form.sections.general.fields.source-location'))
                            ->relationship(
                                'sourceLocation',
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
                            ->required()
                            ->visible(fn (WarehouseSettings $settings, Get $get): bool => $settings->enable_locations && OperationType::withTrashed()->find($get('operation_type_id'))?->type != Enums\OperationType::INCOMING)
                            ->disabled(fn ($record): bool => in_array($record?->state, [OperationState::DONE, OperationState::CANCELED])),
                        Select::make('destination_location_id')
                            ->label(__('inventories::filament/clusters/operations/resources/operation.form.sections.general.fields.destination-location'))
                            ->relationship(
                                'destinationLocation',
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
                            ->required()
                            ->visible(fn (WarehouseSettings $settings, Get $get): bool => $settings->enable_locations && OperationType::withTrashed()->find($get('operation_type_id'))?->type != Enums\OperationType::OUTGOING)
                            ->disabled(fn ($record): bool => in_array($record?->state, [OperationState::DONE, OperationState::CANCELED])),
                    ])
                    ->columns(2),

                Tabs::make()
                    ->schema([
                        Tab::make(__('inventories::filament/clusters/operations/resources/operation.form.tabs.operations.title'))
                            ->schema([
                                OperationResource::getMovesRepeater(),
                            ]),

                        Tab::make(__('inventories::filament/clusters/operations/resources/operation.form.tabs.additional.title'))
                            ->schema([
                                Select::make('user_id')
                                    ->label(__('inventories::filament/clusters/operations/resources/operation.form.tabs.additional.fields.responsible'))
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->default(Auth::id())
                                    ->disabled(fn ($record): bool => in_array($record?->state, [OperationState::DONE, OperationState::CANCELED])),
                                Select::make('move_type')
                                    ->label(__('inventories::filament/clusters/operations/resources/operation.form.tabs.additional.fields.shipping-policy'))
                                    ->options(MoveType::class)
                                    ->default(MoveType::DIRECT)
                                    ->hintIcon('heroicon-m-question-mark-circle', tooltip: __('inventories::filament/clusters/operations/resources/operation.form.tabs.additional.fields.shipping-policy-hint-tooltip'))
                                    ->visible(fn (Get $get): bool => OperationType::withTrashed()->find($get('operation_type_id'))?->type != Enums\OperationType::INCOMING)
                                    ->disabled(fn ($record): bool => in_array($record?->state, [OperationState::DONE, OperationState::CANCELED])),
                                DateTimePicker::make('scheduled_at')
                                    ->label(__('inventories::filament/clusters/operations/resources/operation.form.tabs.additional.fields.scheduled-at'))
                                    ->native(false)
                                    ->default(now()->format('Y-m-d H:i:s'))
                                    ->hintIcon('heroicon-m-question-mark-circle', tooltip: __('inventories::filament/clusters/operations/resources/operation.form.tabs.additional.fields.scheduled-at-hint-tooltip'))
                                    ->disabled(fn ($record): bool => in_array($record?->state, [OperationState::DONE, OperationState::CANCELED])),
                                TextInput::make('origin')
                                    ->label(__('inventories::filament/clusters/operations/resources/operation.form.tabs.additional.fields.source-document'))
                                    ->maxLength(255)
                                    ->hintIcon('heroicon-m-question-mark-circle', tooltip: __('inventories::filament/clusters/operations/resources/operation.form.tabs.additional.fields.source-document-hint-tooltip'))
                                    ->disabled(fn ($record): bool => in_array($record?->state, [OperationState::DONE, OperationState::CANCELED])),
                            ])
                            ->columns(2),

                        Tab::make(__('inventories::filament/clusters/operations/resources/operation.form.tabs.note.title'))
                            ->schema([
                                RichEditor::make('description')
                                    ->hiddenLabel(),
                            ]),
                    ]),
            ])
            ->columns(1);
    }
}
