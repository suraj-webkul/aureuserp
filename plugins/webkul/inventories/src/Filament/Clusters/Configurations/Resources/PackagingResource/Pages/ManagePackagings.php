<?php

namespace Webkul\Inventory\Filament\Clusters\Configurations\Resources\PackagingResource\Pages;

use Webkul\Inventory\Filament\Clusters\Configurations\Resources\PackagingResource;
use Webkul\Product\Filament\Resources\Packaging\Pages\ManagePackagings as BaseManagePackagings;

class ManagePackagings extends BaseManagePackagings
{
    protected static string $resource = PackagingResource::class;
}
