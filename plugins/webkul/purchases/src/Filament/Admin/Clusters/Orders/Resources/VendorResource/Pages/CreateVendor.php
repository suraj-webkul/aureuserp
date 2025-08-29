<?php

namespace Webkul\Purchase\Filament\Admin\Clusters\Orders\Resources\VendorResource\Pages;

use Webkul\Invoice\Filament\Clusters\Vendors\Resources\Vendors\Pages\CreateVendor as BaseCreateVendor;
use Webkul\Purchase\Filament\Admin\Clusters\Orders\Resources\VendorResource;

class CreateVendor extends BaseCreateVendor
{
    protected static string $resource = VendorResource::class;
}
