<?php

namespace Webkul\Account\Filament\Resources\InvoiceResource\Pages;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Webkul\Account\Enums;
use Webkul\Account\Enums\PaymentState;
use Webkul\Account\Filament\Resources\InvoiceResource;
use Webkul\Account\Models\Journal;
use Webkul\Account\Models\Move;
use Webkul\Account\Models\PaymentTerm;
use Webkul\Partner\Models\Partner;
use Webkul\Support\Models\Currency;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('accounts::filament/resources/invoice/pages/create-invoice.notification.title'))
            ->body(__('accounts::filament/resources/invoice/pages/create-invoice.notification.body'));
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        $data['creator_id'] = $user->id;
        $data['state'] ??= Enums\MoveState::DRAFT->value;
        $data['move_type'] ??= Enums\MoveType::OUT_INVOICE->value;
        $data['date'] = now();
        $data['sort'] = Move::max('sort') + 1;

        if ($data['partner_id']) {
            $partner = Partner::find($data['partner_id']);

            $data['invoice_partner_display_name'] = $partner->name;
        } else {
            $data['invoice_partner_display_name'] = "#Created By: {$user->name}";
        }

        return $data;
    }
}
