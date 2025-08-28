<?php

namespace Webkul\Sale\Filament\Clusters\Configuration\Resources\ProductCategoryResource\Pages;

use Webkul\Invoice\Filament\Clusters\Configuration\Resources\ProductCategories\Pages\ListProductCategories as BaseListProductCategories;
use Webkul\Sale\Filament\Clusters\Configuration\Resources\ProductCategoryResource;

class ListProductCategories extends BaseListProductCategories
{
    protected static string $resource = ProductCategoryResource::class;
}
