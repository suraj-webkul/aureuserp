<?php

namespace Webkul\Invoice\Filament\Clusters\Configuration\Resources\TaxResource\Pages;

use Webkul\Account\Filament\Resources\Taxes\TaxResource\Pages\ManageDistributionForRefund as BaseManageDistributionForRefund;
use Webkul\Invoice\Filament\Clusters\Configuration\Resources\TaxResource;

class ManageDistributionForRefund extends BaseManageDistributionForRefund
{
    protected static string $resource = TaxResource::class;
}
