<?php

namespace Webkul\Invoice\Filament\Clusters\Configuration\Resources\TaxResource\Pages;

use Webkul\Account\Filament\Resources\Taxes\Pages\EditTax as BaseEditTax;
use Webkul\Invoice\Filament\Clusters\Configuration\Resources\TaxResource;

class EditTax extends BaseEditTax
{
    protected static string $resource = TaxResource::class;
}
