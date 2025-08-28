<?php

namespace Webkul\Sale\Filament\Clusters\Configuration\Resources\ProductAttributes\Pages;

use Webkul\Invoice\Filament\Clusters\Configuration\Resources\ProductAttributeResource\Pages\ViewProductAttribute as BaseViewProductAttribute;
use Webkul\Sale\Filament\Clusters\Configuration\Resources\ProductAttributes\ProductAttributeResource;

class ViewProductAttribute extends BaseViewProductAttribute
{
    protected static string $resource = ProductAttributeResource::class;
}
