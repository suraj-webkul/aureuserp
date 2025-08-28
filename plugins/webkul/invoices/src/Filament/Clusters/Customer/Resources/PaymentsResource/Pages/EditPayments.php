<?php

namespace Webkul\Invoice\Filament\Clusters\Customer\Resources\PaymentsResource\Pages;

use Webkul\Account\Filament\Resources\Payments\Pages\EditPayments as BaseEditPayments;
use Webkul\Invoice\Filament\Clusters\Customer\Resources\PaymentsResource;

class EditPayments extends BaseEditPayments
{
    protected static string $resource = PaymentsResource::class;
}
