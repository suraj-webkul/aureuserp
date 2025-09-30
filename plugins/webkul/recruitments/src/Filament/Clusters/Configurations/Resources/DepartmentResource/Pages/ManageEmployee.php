<?php

namespace Webkul\Recruitment\Filament\Clusters\Configurations\Resources\DepartmentResource\Pages;

use Filament\Pages\Enums\SubNavigationPosition;
use Webkul\Employee\Filament\Resources\DepartmentResource\Pages\ManageEmployee as BaseManageEmployee;
use Webkul\Recruitment\Filament\Clusters\Configurations\Resources\DepartmentResource;

class ManageEmployee extends BaseManageEmployee
{
    protected static string $resource = DepartmentResource::class;

    public static function getSubNavigationPosition(): SubNavigationPosition
    {
        return SubNavigationPosition::Top;
    }
}
