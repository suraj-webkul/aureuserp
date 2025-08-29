<?php

namespace Webkul\Purchase\Filament\Admin\Clusters\Orders\Resources\VendorResource\Pages;

use Webkul\Invoice\Filament\Clusters\Vendors\Resources\Vendors\Pages\ManageContacts as BaseManageContacts;
use Webkul\Purchase\Filament\Admin\Clusters\Orders\Resources\VendorResource;

class ManageContacts extends BaseManageContacts
{
    protected static string $resource = VendorResource::class;
}
