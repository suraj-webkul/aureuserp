<?php

namespace Webkul\Support\Filament\Resources\ActivityType;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Webkul\Support\Filament\Resources\ActivityType\Pages\CreateActivityType;
use Webkul\Support\Filament\Resources\ActivityType\Pages\EditActivityType;
use Webkul\Support\Filament\Resources\ActivityType\Pages\ListActivityTypes;
use Webkul\Support\Filament\Resources\ActivityType\Pages\ViewActivityType;
use Webkul\Support\Models\ActivityType;

class ActivityTypeResource extends Resource
{
    protected static ?string $model = ActivityType::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $slug = 'settings/activity-types';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return ActivityTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivityTypesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ActivityTypeInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListActivityTypes::route('/'),
            'create' => CreateActivityType::route('/create'),
            'view'   => ViewActivityType::route('/{record}'),
            'edit'   => EditActivityType::route('/{record}/edit'),
        ];
    }
}
