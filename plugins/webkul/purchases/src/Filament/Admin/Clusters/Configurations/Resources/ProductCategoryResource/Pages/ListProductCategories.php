<?php

namespace Webkul\Purchase\Filament\Admin\Clusters\Configurations\Resources\ProductCategoryResource\Pages;

use Webkul\Product\Filament\Resources\Categories\Pages\ListCategories;
use Webkul\Purchase\Filament\Admin\Clusters\Configurations\Resources\ProductCategoryResource;

class ListProductCategories extends ListCategories
{
    protected static string $resource = ProductCategoryResource::class;
}
