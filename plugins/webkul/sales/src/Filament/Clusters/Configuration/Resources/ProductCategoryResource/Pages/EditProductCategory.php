<?php

namespace Webkul\Sale\Filament\Clusters\Configuration\Resources\ProductCategoryResource\Pages;

use Filament\Pages\Enums\SubNavigationPosition;
use Webkul\Invoice\Filament\Clusters\Configuration\Resources\ProductCategoryResource\Pages\EditProductCategory as BaseEditProductCategory;
use Webkul\Sale\Filament\Clusters\Configuration\Resources\ProductCategoryResource;
use Webkul\Sale\Filament\Widgets\RecordNavigationTabs;

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

    protected function getHeaderWidgets(): array
    {
        $navigationItems = static::getResource()::getRecordSubNavigation($this);
        $livewireFriendlyNavItems = collect($navigationItems)->map(function ($item) {
            return [
                'label'    => $item->getLabel(),
                'url'      => $item->getUrl(),
                'isActive' => $item->isActive(),
                'isHidden' => $item->isHidden(),
                'icon'     => $item->getIcon(),
                'badge'    => $item->getBadge(),
            ];
        })->toArray();

        return [
            RecordNavigationTabs::make([
                'navigationItems' => $livewireFriendlyNavItems,
            ]),
        ];
    }
}
