<?php

namespace Webkul\Invoice\Filament\Clusters\Configuration\Resources\BankAccountResource\Pages;

use Webkul\Account\Filament\Resources\BankAccounts\Pages\ListBankAccounts as BaseManageBankAccounts;
use Webkul\Invoice\Filament\Clusters\Configuration\Resources\BankAccountResource;

class ListBankAccounts extends BaseManageBankAccounts
{
    protected static string $resource = BankAccountResource::class;
}
