<?php

namespace Webkul\Account\Filament\Resources\BankAccountResource\Pages;

use Webkul\Account\Filament\Resources\BankAccountResource;
use Webkul\Partner\Filament\Resources\BankAccount\Pages\ManageBankAccounts as BaseManageBankAccounts;

class ListBankAccounts extends BaseManageBankAccounts
{
    protected static string $resource = BankAccountResource::class;
}
