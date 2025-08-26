<?php

namespace Webkul\Invoice\Filament\Clusters\Vendors\Resources\BillResource\Pages;

use Webkul\Account\Filament\Resources\Bills\Pages\CreateBill as BaseCreateBill;
use Webkul\Invoice\Filament\Clusters\Vendors\Resources\BillResource;

class CreateBill extends BaseCreateBill
{
    protected static string $resource = BillResource::class;
}
