<?php

namespace Webkul\Website\Filament\Admin\Resources\Partner\Pages;

use Webkul\Partner\Filament\Resources\PartnerResource\Pages\ViewPartner as BaseViewPartner;
use Webkul\Website\Filament\Admin\Resources\Partner\PartnerResource;

class ViewPartner extends BaseViewPartner
{
    protected static string $resource = PartnerResource::class;
}
