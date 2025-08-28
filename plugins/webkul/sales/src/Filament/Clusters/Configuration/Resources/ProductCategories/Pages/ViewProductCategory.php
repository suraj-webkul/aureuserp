<?php

namespace Webkul\Sale\Filament\Clusters\Configuration\Resources\ProductCategories\Pages;

use Webkul\Sale\Filament\Clusters\Configuration\Resources\ProductCategories\ProductCategoryResource;
use Webkul\Invoice\Filament\Clusters\Configuration\Resources\ProductCategories\Pages\ViewProductCategory as BaseViewProductCategory;

class ViewProductCategory extends BaseViewProductCategory
{
    protected static string $resource = ProductCategoryResource::class;
}
