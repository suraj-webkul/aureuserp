<?php

namespace Webkul\Invoice\Filament\Clusters\Customer\Resources\CreditNotesResource\Pages;

use Webkul\Account\Filament\Resources\CreditNotes\Pages\CreateCreditNote as BaseCreateInvoice;
use Webkul\Invoice\Filament\Clusters\Customer\Resources\CreditNotesResource;

class CreateCreditNotes extends BaseCreateInvoice
{
    protected static string $resource = CreditNotesResource::class;
}
