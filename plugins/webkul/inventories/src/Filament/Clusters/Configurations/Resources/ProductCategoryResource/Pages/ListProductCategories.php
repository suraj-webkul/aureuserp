<?php

namespace Webkul\Inventory\Filament\Clusters\Configurations\Resources\ProductCategoryResource\Pages;

use Webkul\Inventory\Filament\Clusters\Configurations\Resources\ProductCategoryResource;
use Webkul\Product\Filament\Resources\Categories\Pages\ListCategories;

class ListProductCategories extends ListCategories
{
    protected static string $resource = ProductCategoryResource::class;
}
