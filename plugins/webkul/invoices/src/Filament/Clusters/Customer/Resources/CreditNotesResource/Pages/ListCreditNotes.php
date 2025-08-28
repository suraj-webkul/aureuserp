<?php

namespace Webkul\Invoice\Filament\Clusters\Customer\Resources\CreditNotesResource\Pages;

use Webkul\Account\Filament\Resources\CreditNotes\Pages\ListCreditNotes as BaseListInvoices;
use Webkul\Invoice\Filament\Clusters\Customer\Resources\CreditNotesResource;

class ListCreditNotes extends BaseListInvoices
{
    protected static string $resource = CreditNotesResource::class;
}
