<?php

namespace Webkul\Purchase\Filament\Admin\Clusters\Products\Resources\ProductResource\Pages;

use Webkul\Product\Filament\Resources\Products\Pages\EditProduct as BaseEditProduct;
use Webkul\Purchase\Filament\Admin\Clusters\Products\Resources\ProductResource;

class EditProduct extends BaseEditProduct
{
    protected static string $resource = ProductResource::class;
}
