<?php

namespace Webkul\Purchase\Filament\Admin\Clusters\Orders\Resources\VendorResource\Pages;

use Webkul\Invoice\Filament\Clusters\Vendors\Resources\Vendors\Pages\EditVendor as BaseEditVendor;
use Webkul\Purchase\Filament\Admin\Clusters\Orders\Resources\VendorResource;

class EditVendor extends BaseEditVendor
{
    protected static string $resource = VendorResource::class;
}
