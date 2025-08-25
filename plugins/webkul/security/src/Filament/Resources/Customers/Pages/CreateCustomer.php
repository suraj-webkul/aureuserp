<?php

namespace Webkul\Security\Filament\Resources\Customers\Pages;

use Filament\Resources\Pages\CreateRecord;
use Webkul\Security\Filament\Resources\Customers\CustomerResource;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;
}
