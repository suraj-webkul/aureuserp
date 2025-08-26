<?php

namespace Webkul\Invoice\Filament\Clusters\Vendors\Resources\BillResource\Pages;

use Webkul\Account\Filament\Resources\Bills\Pages\ListBills as BaseListBills;
use Webkul\Invoice\Filament\Clusters\Vendors\Resources\BillResource;

class ListBills extends BaseListBills
{
    protected static string $resource = BillResource::class;
}
