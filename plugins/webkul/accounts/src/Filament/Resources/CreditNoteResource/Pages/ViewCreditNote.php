<?php

namespace Webkul\Account\Filament\Resources\CreditNoteResource\Pages;

use Webkul\Account\Filament\Resources\CreditNoteResource;
use Webkul\Account\Filament\Resources\InvoiceResource\Pages\ViewInvoice as ViewRecord;

class ViewCreditNote extends ViewRecord
{
    protected static string $resource = CreditNoteResource::class;

    protected function getHeaderActions(): array
    {
        $predefinedActions = parent::getHeaderActions();

        $predefinedActions = collect($predefinedActions)->filter(function ($action) {
            return ! in_array($action->getName(), [
                'customers.invoice.set-as-checked',
                'customers.invoice.credit-note'
            ]);
        })->toArray();

        return $predefinedActions;
    }
}
