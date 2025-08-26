<?php

namespace Webkul\Contact\Filament\Resources\PartnerResource\Pages;

use Webkul\Contact\Filament\Resources\PartnerResource;
use Webkul\Partner\Filament\Resources\Partner\Pages\CreatePartner as BaseCreatePartner;

class CreatePartner extends BaseCreatePartner
{
    protected static string $resource = PartnerResource::class;
}
