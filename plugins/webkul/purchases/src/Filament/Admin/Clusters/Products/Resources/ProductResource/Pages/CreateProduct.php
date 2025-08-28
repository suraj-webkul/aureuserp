<?php

namespace Webkul\Purchase\Filament\Admin\Clusters\Products\Resources\ProductResource\Pages;

use Webkul\Product\Filament\Resources\Products\Pages\CreateProduct as BaseCreateProduct;
use Webkul\Purchase\Filament\Admin\Clusters\Products\Resources\ProductResource;

class CreateProduct extends BaseCreateProduct
{
    protected static string $resource = ProductResource::class;
}
