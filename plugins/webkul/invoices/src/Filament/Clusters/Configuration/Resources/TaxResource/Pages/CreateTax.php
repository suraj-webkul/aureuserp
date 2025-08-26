<?php

namespace Webkul\Invoice\Filament\Clusters\Configuration\Resources\TaxResource\Pages;

use Webkul\Account\Filament\Resources\Taxes\TaxResource\Pages\CreateTax as BaseCreateTax;
use Webkul\Invoice\Filament\Clusters\Configuration\Resources\TaxResource;

class CreateTax extends BaseCreateTax
{
    protected static string $resource = TaxResource::class;
}
