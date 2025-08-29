<?php

namespace Webkul\Invoice\Filament\Clusters\Vendors\Resources\Vendors\Pages;

use Webkul\Invoice\Filament\Clusters\Vendors\Resources\Vendors\VendorResource;
use Webkul\Partner\Filament\Resources\Partners\Pages\ManageContacts as BaseManageContacts;

class ManageContacts extends BaseManageContacts
{
    protected static string $resource = VendorResource::class;
}
