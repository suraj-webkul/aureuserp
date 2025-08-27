<?php

namespace Webkul\Invoice\Filament\Clusters\Configuration\Resources\PaymentTermResource\Pages;

use Webkul\Account\Filament\Resources\PaymentTerms\Pages\ManagePaymentDueTerm as BaseManagePaymentDueTerm;
use Webkul\Invoice\Filament\Clusters\Configuration\Resources\PaymentTermResource;

class ManagePaymentDueTerm extends BaseManagePaymentDueTerm
{
    protected static string $resource = PaymentTermResource::class;
}
