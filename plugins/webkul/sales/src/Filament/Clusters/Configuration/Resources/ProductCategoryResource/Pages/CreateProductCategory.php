<?php

namespace Webkul\Sale\Filament\Clusters\Configuration\Resources\ProductCategoryResource\Pages;

use Webkul\Invoice\Filament\Clusters\Configuration\Resources\ProductCategories\Pages\CreateProductCategory as BaseCreateProductCategory;
use Webkul\Sale\Filament\Clusters\Configuration\Resources\ProductCategoryResource;

class CreateProductCategory extends BaseCreateProductCategory
{
    protected static string $resource = ProductCategoryResource::class;
}
