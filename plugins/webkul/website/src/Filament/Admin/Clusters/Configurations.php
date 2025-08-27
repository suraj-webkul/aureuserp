<?php

namespace Webkul\Website\Filament\Admin\Clusters;

use Filament\Clusters\Cluster;
use BackedEnum;

class Configurations extends Cluster
{
    protected static ?string $slug = 'website/configurations';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('website::filament/admin/clusters/configurations.navigation.title');
    }

    public static function getNavigationGroup(): string
    {
        return __('website::filament/admin/clusters/configurations.navigation.group');
    }
}
