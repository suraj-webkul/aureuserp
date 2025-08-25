<?php

namespace Webkul\Security\Filament\Resources\Roles\Schemas;

use Filament\Schemas\Schema;
use BezhanSalleh\FilamentShield\Resources\RoleResource;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return RoleResource::form($schema);
    }
}
