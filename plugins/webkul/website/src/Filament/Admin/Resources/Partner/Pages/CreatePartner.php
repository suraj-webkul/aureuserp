<?php

namespace Webkul\Website\Filament\Admin\Resources\Partner\Pages;

use Webkul\Partner\Filament\Resources\PartnerResource\Pages\CreatePartner as BaseCreatePartner;
use Webkul\Website\Filament\Admin\Resources\Partner\PartnerResource;

class CreatePartner extends BaseCreatePartner
{
    protected static string $resource = PartnerResource::class;
}
