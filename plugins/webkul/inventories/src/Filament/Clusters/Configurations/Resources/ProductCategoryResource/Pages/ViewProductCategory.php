<?php

namespace Webkul\Inventory\Filament\Clusters\Configurations\Resources\ProductCategoryResource\Pages;

use Webkul\Inventory\Filament\Clusters\Configurations\Resources\ProductCategoryResource;
use Webkul\Product\Filament\Resources\Categories\Pages\ViewCategory;

class ViewProductCategory extends ViewCategory
{
    protected static string $resource = ProductCategoryResource::class;
}
