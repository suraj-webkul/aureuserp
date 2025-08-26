<?php

namespace Webkul\Invoice\Filament\Clusters\Configuration\Resources\TaxGroupResource\Pages;

use Webkul\Account\Filament\Resources\TaxGroups\Pages\CreateTaxGroup as BaseCreateTaxGroup;
use Webkul\Invoice\Filament\Clusters\Configuration\Resources\TaxGroupResource;

class CreateTaxGroup extends BaseCreateTaxGroup
{
    protected static string $resource = TaxGroupResource::class;
}
