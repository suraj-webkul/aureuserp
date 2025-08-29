<?php

namespace Webkul\Invoice\Filament\Clusters\Vendors\Resources\Vendors\Pages;

use Webkul\Invoice\Filament\Clusters\Vendors\Resources\Vendors\VendorResource;
use Webkul\Partner\Filament\Resources\Partners\Pages\ManageAddresses as BaseManageAddresses;

class ManageAddresses extends BaseManageAddresses
{
    protected static string $resource = VendorResource::class;
}
