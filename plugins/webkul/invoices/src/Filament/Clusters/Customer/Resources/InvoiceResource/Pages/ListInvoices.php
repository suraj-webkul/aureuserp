<?php

namespace Webkul\Invoice\Filament\Clusters\Customer\Resources\InvoiceResource\Pages;

use Webkul\Account\Filament\Resources\Invoices\Pages\ListInvoices as BaseListInvoices;
use Webkul\Invoice\Filament\Clusters\Customer\Resources\InvoiceResource;

class ListInvoices extends BaseListInvoices
{
    protected static string $resource = InvoiceResource::class;
}
