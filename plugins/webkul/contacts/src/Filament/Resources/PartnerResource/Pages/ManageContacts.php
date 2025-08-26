<?php

namespace Webkul\Contact\Filament\Resources\PartnerResource\Pages;

use Webkul\Contact\Filament\Resources\PartnerResource;
use Webkul\Partner\Filament\Resources\Partner\Pages\ManageContacts as BaseManageContacts;

class ManageContacts extends BaseManageContacts
{
    protected static string $resource = PartnerResource::class;
}
