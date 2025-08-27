<?php

namespace Webkul\Invoice\Filament\Clusters\Configuration\Resources\ProductAttributeResource\Pages;

use Webkul\Invoice\Filament\Clusters\Configuration\Resources\ProductAttributeResource;
use Webkul\Product\Filament\Resources\Attributes\Pages\EditAttribute;

class EditProductAttribute extends EditAttribute
{
    protected static string $resource = ProductAttributeResource::class;
}
