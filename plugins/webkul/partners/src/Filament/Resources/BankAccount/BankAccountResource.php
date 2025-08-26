<?php

namespace Webkul\Partner\Filament\Resources\BankAccount;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Webkul\Partner\Models\BankAccount;
use Webkul\Partner\Filament\Resources\BankAccount\Schemas\BankAccountForm;
use Webkul\Partner\Filament\Resources\BankAccount\Tables\BankAccountTable;

class BankAccountResource extends Resource
{
    protected static ?string $model = BankAccount::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    public static function getNavigationGroup(): string
    {
        return __('partners::filament/resources/bank-account.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('partners::filament/resources/bank-account.navigation.title');
    }

    public static function form(Schema $schema): Schema
    {
        return BankAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BankAccountTable::configure($table);
    }
}
