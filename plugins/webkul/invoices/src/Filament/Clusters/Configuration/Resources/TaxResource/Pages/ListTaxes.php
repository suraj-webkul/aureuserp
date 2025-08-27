<?php

namespace Webkul\Invoice\Filament\Clusters\Configuration\Resources\TaxResource\Pages;

use Webkul\Account\Filament\Resources\Taxes\Pages\ListTaxes as BaseListTaxes;
use Webkul\Invoice\Filament\Clusters\Configuration\Resources\TaxResource;

class ListTaxes extends BaseListTaxes
{
    protected static string $resource = TaxResource::class;
}
