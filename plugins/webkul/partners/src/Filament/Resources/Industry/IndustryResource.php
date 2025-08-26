<?php

namespace Webkul\Partner\Filament\Resources\Industry;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Webkul\Partner\Models\Industry;
use Webkul\Partners\Filament\Resources\Industry\Schemas\IndustryForm;
use Webkul\Partners\Filament\Resources\Industry\Table\IndustryTable;

class IndustryResource extends Resource
{
    protected static ?string $model = Industry::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationLabel(): string
    {
        return __('partners::filament/resources/industry.navigation.title');
    }

    public static function form(Schema $schema): Schema
    {
        return IndustryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IndustryTable::configure($table);
    }
}
