<?php

namespace Webkul\Security\Filament\Resources\Roles\Tables;

use Filament\Tables\Table;
use BezhanSalleh\FilamentShield\Resources\RoleResource;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return RoleResource::table($table);
    }
}
