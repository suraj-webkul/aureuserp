<?php

namespace Webkul\Partner\Filament\Resources\Title;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Webkul\Partner\Models\Title;
use Webkul\Partners\Filament\Resources\Title\Schemas\TitleForm;
use Webkul\Partners\Filament\Resources\Title\Table\TitleTable;

class TitleResource extends Resource
{
    protected static ?string $model = Title::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return TitleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TitleTable::configure($table);
    }
}
