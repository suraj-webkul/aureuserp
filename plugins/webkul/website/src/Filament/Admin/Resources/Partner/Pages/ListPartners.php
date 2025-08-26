<?php

namespace Webkul\Website\Filament\Admin\Resources\Partner\Pages;

use Webkul\Partner\Filament\Resources\Partner\Pages\ListPartners as BaseListPartners;
use Webkul\Website\Filament\Admin\Resources\Partner\PartnerResource;

class ListPartners extends BaseListPartners
{
    protected static string $resource = PartnerResource::class;
}
