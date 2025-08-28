<?php

namespace Webkul\Invoice\Filament\Clusters\Customer\Resources\InvoiceResource\Pages;

use Webkul\Account\Filament\Resources\Invoices\Pages\CreateInvoice as BaseCreateInvoice;
use Webkul\Invoice\Filament\Clusters\Customer\Resources\InvoiceResource;

class CreateInvoice extends BaseCreateInvoice
{
    protected static string $resource = InvoiceResource::class;
}
