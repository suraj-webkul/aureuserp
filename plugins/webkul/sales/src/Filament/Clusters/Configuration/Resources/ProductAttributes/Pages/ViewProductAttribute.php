<?php

namespace Webkul\Sale\Filament\Clusters\Configuration\Resources\ProductAttributes\Pages;

use Webkul\Sale\Filament\Clusters\Configuration\Resources\ProductAttributes\ProductAttributeResource;
use Webkul\Invoice\Filament\Clusters\Configuration\Resources\ProductAttributes\Pages\ViewProductAttribute as BaseViewProductAttribute;

class ViewProductAttribute extends BaseViewProductAttribute
{
    protected static string $resource = ProductAttributeResource::class;
}
