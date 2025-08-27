<?php

namespace Webkul\Invoice\Filament\Clusters\Configuration\Resources\TaxResource\Pages;

use Webkul\Account\Filament\Resources\Taxes\Pages\ManageDistributionForInvoice as BaseManageDistributionForInvoice;
use Webkul\Invoice\Filament\Clusters\Configuration\Resources\TaxResource;

class ManageDistributionForInvoice extends BaseManageDistributionForInvoice
{
    protected static string $resource = TaxResource::class;
}
