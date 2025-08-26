<?php

namespace Webkul\Partner\Filament\Resources\Partner;

use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Webkul\Partner\Models\Partner;
use Webkul\Partners\Filament\Resources\Partner\Schemas\PartnerForm;
use Webkul\Partners\Filament\Resources\Partner\Schemas\PartnerInfolist;
use Webkul\Partners\Filament\Resources\Partner\Tables\PartnerTable;
use Filament\Resources\Resource;

class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return PartnerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PartnerTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PartnerInfolist::configure($schema);
    }
}
