<?php

namespace Webkul\Sale\Filament\Clusters\Configuration\Resources\ProductCategoryResource\Pages;

use Filament\Pages\Enums\SubNavigationPosition;
use Webkul\Invoice\Filament\Clusters\Configuration\Resources\ProductCategoryResource\Pages\EditProductCategory as BaseEditProductCategory;
use Webkul\Sale\Filament\Clusters\Configuration\Resources\ProductCategoryResource;

class EditProductCategory extends BaseEditProductCategory
{
    protected static string $resource = ProductCategoryResource::class;
    public static function getSubNavigationPosition(): SubNavigationPosition
    {

        return SubNavigationPosition::Start;
    }
    public function getSubNavigation(): array
    {
        if (filled($cluster = static::getCluster())) {
            return $this->generateNavigationItems($cluster::getClusteredComponents());
        }

        return [];
    }

    public function getRecordTabs(): array
    {
        return static::getResource()::getRecordSubNavigation($this);
    }
    protected string $view = 'support::filament.pages.edit-with-tabs';
}
