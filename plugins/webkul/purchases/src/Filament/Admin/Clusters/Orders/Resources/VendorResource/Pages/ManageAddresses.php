<?php

namespace Webkul\Purchase\Filament\Admin\Clusters\Orders\Resources\VendorResource\Pages;

use Webkul\Invoice\Filament\Clusters\Vendors\Resources\Vendors\Pages\ManageAddresses as BaseManageAddresses;
use Webkul\Purchase\Filament\Admin\Clusters\Orders\Resources\VendorResource;

class ManageAddresses extends BaseManageAddresses
{
    protected static string $resource = VendorResource::class;
}
