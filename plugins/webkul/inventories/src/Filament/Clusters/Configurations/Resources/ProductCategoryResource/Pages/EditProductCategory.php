<?php

namespace Webkul\Inventory\Filament\Clusters\Configurations\Resources\ProductCategoryResource\Pages;

use Webkul\Inventory\Filament\Clusters\Configurations\Resources\ProductCategoryResource;
use Webkul\Product\Filament\Resources\Categories\Pages\EditCategory;

class EditProductCategory extends EditCategory
{
    protected static string $resource = ProductCategoryResource::class;
}
