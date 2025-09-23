<?php

namespace Webkul\TimeOff\Filament\Clusters\MyTime\Resources;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Webkul\TimeOff\Enums\RequestDateFromPeriod;
use Webkul\TimeOff\Enums\State;
use Webkul\TimeOff\Filament\Clusters\Management\Resources\TimeOffResource;
use Webkul\TimeOff\Filament\Clusters\MyTime;
use Webkul\TimeOff\Filament\Clusters\MyTime\Resources\MyTimeOffResource\Pages\CreateMyTimeOff;
use Webkul\TimeOff\Filament\Clusters\MyTime\Resources\MyTimeOffResource\Pages\EditMyTimeOff;
use Webkul\TimeOff\Filament\Clusters\MyTime\Resources\MyTimeOffResource\Pages\ListMyTimeOffs;
use Webkul\TimeOff\Filament\Clusters\MyTime\Resources\MyTimeOffResource\Pages\ViewMyTimeOff;
use Webkul\TimeOff\Models\Leave;
use Webkul\TimeOff\Models\LeaveType;

class MyTimeOffResource extends Resource
{
    protected static ?string $model = Leave::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-lifebuoy';

    protected static ?int $navigationSort = 2;

    protected static ?string $cluster = MyTime::class;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getModelLabel(): string
    {
        return __('time-off::filament/clusters/my-time/resources/my-time-off.model-label');
    }

    public static function getNavigationLabel(): string
    {
        return __('time-off::filament/clusters/my-time/resources/my-time-off.navigation.title');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->disabled(fn (?Leave $record) => ! static::isEditableState($record))
            ->components([
                Section::make()
                    ->schema([
                        Group::make()
                            ->schema([
                                Select::make('holiday_status_id')
                                    ->relationship('holidayStatus', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->label(__('time-off::filament/clusters/my-time/resources/my-time-off.form.fields.time-off-type'))
                                    ->required(),
                                Fieldset::make()
                                    ->label(function (Get $get) {
                                        if ($get('request_unit_half')) {
                                            return __('time-off::filament/clusters/my-time/resources/my-time-off.form.fields.date');
                                        } else {
                                            return __('time-off::filament/clusters/my-time/resources/my-time-off.form.fields.dates');
                                        }
                                    })
                                    ->live()
                                    ->schema([
                                        DatePicker::make('request_date_from')
                                            ->native(false)
                                            ->live()
                                            ->default(now())
                                            ->label(__('time-off::filament/clusters/my-time/resources/my-time-off.form.fields.request-date-from'))
                                            ->required(),
                                        DatePicker::make('request_date_to')
                                            ->native(false)
                                            ->live()
                                            ->default(now())
                                            ->label(__('time-off::filament/clusters/my-time/resources/my-time-off.form.fields.request-date-to'))
                                            ->hidden(fn (Get $get) => $get('request_unit_half'))
                                            ->required()
                                            ->minDate(fn (Get $get) => $get('request_date_from'))
                                            ->disabled(fn (Get $get) => blank($get('request_date_from')))
                                            ->rule(function (Get $get) {
                                                return function (string $attribute, $value, \Closure $fail) use ($get) {
                                                    $from = $get('request_date_from');
                                                    if ($from && $value && Carbon::parse($value)->lt(Carbon::parse($from))) {
                                                        $fail(__('The end date cannot be earlier than the start date.'));
                                                    }
                                                };
                                            }),
                                        Select::make('request_date_from_period')
                                            ->options(RequestDateFromPeriod::class)
                                            ->default(RequestDateFromPeriod::MORNING)
                                            ->native(false)
                                            ->label(__('time-off::filament/clusters/my-time/resources/my-time-off.form.fields.period'))
                                            ->visible(fn (Get $get) => $get('request_unit_half'))
                                            ->required(),
                                    ]),
                                Toggle::make('request_unit_half')
                                    ->live()
                                    ->label(__('time-off::filament/clusters/my-time/resources/my-time-off.form.fields.half-day')),
                                TextEntry::make('duration_info')
                                    ->label(__('time-off::filament/clusters/my-time/resources/my-time-off.form.fields.requested-days'))
                                    ->live()
                                    ->state(function (Get $get): string {
                                        if ($get('request_unit_half')) {
                                            return __('time-off::filament/clusters/my-time/resources/my-time-off.form.fields.day', ['day' => '0.5']);
                                        }

                                        $startDate = $get('request_date_from');
                                        $endDate = $get('request_date_to');

                                        if (! $startDate) {
                                            return __('time-off::filament/clusters/my-time/resources/my-time-off.form.fields.days', ['days' => 0]);
                                        }

                                        try {
                                            $startDate = Carbon::parse($startDate);
                                            $endDate = $endDate ? Carbon::parse($endDate) : $startDate;
                                            $days = $startDate->diffInDays($endDate) + 1;

                                            return __('time-off::filament/clusters/my-time/resources/my-time-off.form.fields.days', ['days' => $days]);
                                        } catch (\Exception $e) {
                                            return __('time-off::filament/clusters/my-time/resources/my-time-off.form.fields.days', ['days' => 0]);
                                        }
                                    }),
                                Textarea::make('private_name')
                                    ->label(__('time-off::filament/clusters/my-time/resources/my-time-off.form.fields.description'))
                                    ->live(),
                                FileUpload::make('attachment')
                                    ->label(__('time-off::filament/clusters/my-time/resources/my-time-off.form.fields.attachment'))
                                    ->acceptedFileTypes([
                                        'image/*',
                                        'application/pdf',
                                    ])
                                    ->visible(function (Get $get) {
                                        $leaveType = LeaveType::find($get('holiday_status_id'));

                                        if ($leaveType) {
                                            return $leaveType->support_document;
                                        }

                                        return false;
                                    })
                                    ->live(),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table = TimeOffResource::table($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListMyTimeOffs::route('/'),
            'create' => CreateMyTimeOff::route('/create'),
            'edit'   => EditMyTimeOff::route('/{record}/edit'),
            'view'   => ViewMyTimeOff::route('/{record}'),
        ];
    }

    public static function isEditableState(?Leave $record): bool
    {
        return ! in_array($record?->state, [
            State::REFUSE,
            State::VALIDATE_ONE,
            State::VALIDATE_TWO,
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Group::make()
                            ->schema([
                                TextEntry::make('holidayStatus.name')
                                    ->label(__('time-off::filament/clusters/management/resources/time-off.infolist.entries.time-off-type'))
                                    ->icon('heroicon-o-calendar'),
                                TextEntry::make('request_unit_half')
                                    ->label(__('time-off::filament/clusters/management/resources/time-off.infolist.entries.half-day'))
                                    ->formatStateUsing(fn ($record) => $record->request_unit_half ? 'Yes' : 'No')
                                    ->icon('heroicon-o-clock'),
                                TextEntry::make('request_date_from')
                                    ->label(__('time-off::filament/clusters/management/resources/time-off.infolist.entries.request-date-from'))
                                    ->date()
                                    ->icon('heroicon-o-calendar'),
                                TextEntry::make('request_date_to')
                                    ->label(__('time-off::filament/clusters/management/resources/time-off.infolist.entries.request-date-to'))
                                    ->date()
                                    ->hidden(fn ($record) => $record->request_unit_half)
                                    ->icon('heroicon-o-calendar'),
                                TextEntry::make('request_date_from_period')
                                    ->label(__('time-off::filament/clusters/management/resources/time-off.infolist.entries.period'))
                                    ->visible(fn ($record) => $record->request_unit_half)
                                    ->icon('heroicon-o-sun'),
                                TextEntry::make('private_name')
                                    ->label(__('time-off::filament/clusters/management/resources/time-off.infolist.entries.description'))
                                    ->icon('heroicon-o-document-text'),
                                TextEntry::make('duration_display')
                                    ->label(__('time-off::filament/clusters/management/resources/time-off.infolist.entries.requested-days'))
                                    ->formatStateUsing(function ($record) {
                                        if ($record->request_unit_half) {
                                            return __('time-off::filament/clusters/management/resources/time-off.infolist.entries.day', ['day' => '0.5']);
                                        }

                                        $startDate = Carbon::parse($record->request_date_from);
                                        $endDate = $record->request_date_to ? Carbon::parse($record->request_date_to) : $startDate;

                                        return __('time-off::filament/clusters/management/resources/time-off.infolist.entries.days', ['days' => ($startDate->diffInDays($endDate) + 1)]);
                                    })
                                    ->icon('heroicon-o-calendar-days'),
                                ImageEntry::make('attachment')
                                    ->label(__('time-off::filament/clusters/management/resources/time-off.infolist.entries.attachment'))
                                    ->visible(fn ($record) => $record->holidayStatus?->support_document),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
