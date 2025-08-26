<?php

namespace Webkul\Partner\Filament\Resources\Address;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Webkul\Partner\Models\Partner;
use Webkul\Partner\Filament\Resources\Address\Schemas\AddressForm;
use Webkul\Partner\Filament\Resources\Address\Table\AddressTable;

class AddressResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return AddressForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AddressTable::configure($table);
    }
}
