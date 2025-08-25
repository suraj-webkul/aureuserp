<?php

namespace Webkul\Security\Filament\Resources\Customers;

use App\Models\Customer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Webkul\Security\Filament\Resources\Customers\Pages\CreateCustomer;
use Webkul\Security\Filament\Resources\Customers\Pages\EditCustomer;
use Webkul\Security\Filament\Resources\Customers\Pages\ListCustomers;
use Webkul\Security\Filament\Resources\Customers\Schemas\CustomerForm;
use Webkul\Security\Filament\Resources\Customers\Tables\CustomersTable;
use Webkul\Security\Models\User;

class CustomerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'User';

    public static function form(Schema $schema): Schema
    {
        return CustomerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }
}
