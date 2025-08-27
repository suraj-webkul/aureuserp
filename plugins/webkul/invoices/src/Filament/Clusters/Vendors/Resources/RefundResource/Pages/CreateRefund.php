<?php

namespace Webkul\Invoice\Filament\Clusters\Vendors\Resources\RefundResource\Pages;

use Webkul\Account\Filament\Resources\Refunds\Pages\CreateRefund as BaseCreateRefund;
use Webkul\Invoice\Filament\Clusters\Vendors\Resources\RefundResource;

class CreateRefund extends BaseCreateRefund
{
    protected static string $resource = RefundResource::class;
}
