<?php

namespace Webkul\Contact\Filament\Resources\PartnerResource\Pages;

use Webkul\Contact\Filament\Resources\PartnerResource;
use Webkul\Partner\Filament\Resources\Partner\Pages\EditPartner as BaseEditPartner;

class EditPartner extends BaseEditPartner
{
    protected static string $resource = PartnerResource::class;
}
