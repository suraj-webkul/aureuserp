<?php

namespace Webkul\Invoice\Filament\Clusters\Customer\Resources\PaymentsResource\Pages;

use Webkul\Account\Filament\Resources\Payments\Pages\ViewPayments as BaseViewPayments;
use Webkul\Invoice\Filament\Clusters\Customer\Resources\PaymentsResource;

class ViewPayments extends BaseViewPayments
{
    protected static string $resource = PaymentsResource::class;
}
