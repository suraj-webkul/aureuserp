<?php

namespace Webkul\Invoice\Filament\Clusters\Configuration\Resources\PaymentTermResource\Pages;

use Webkul\Account\Filament\Resources\PaymentTerms\Pages\ViewPaymentTerm as BaseViewPaymentTerm;
use Webkul\Invoice\Filament\Clusters\Configuration\Resources\PaymentTermResource;

class ViewPaymentTerm extends BaseViewPaymentTerm
{
    protected static string $resource = PaymentTermResource::class;
}
