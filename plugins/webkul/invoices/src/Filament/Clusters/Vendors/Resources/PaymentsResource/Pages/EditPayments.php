<?php

namespace Webkul\Invoice\Filament\Clusters\Vendors\Resources\PaymentsResource\Pages;

use Webkul\Account\Filament\Resources\Payments\Pages\EditPayments as BaseEditPayments;
use Webkul\Invoice\Filament\Clusters\Vendors\Resources\PaymentsResource;

class EditPayments extends BaseEditPayments
{
    protected static string $resource = PaymentsResource::class;
}
