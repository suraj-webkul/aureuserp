<?php

namespace Webkul\Invoice\Filament\Clusters\Configuration\Resources\TaxResource\Pages;

use Webkul\Account\Filament\Resources\Taxes\TaxResource\Pages\ViewTax as BaseViewTax;
use Webkul\Invoice\Filament\Clusters\Configuration\Resources\TaxResource;

class ViewTax extends BaseViewTax
{
    protected static string $resource = TaxResource::class;
}
