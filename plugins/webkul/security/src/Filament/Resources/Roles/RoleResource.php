<?php

namespace Webkul\Security\Filament\Resources\Roles;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Webkul\Security\Filament\Resources\Roles\Schemas\RoleForm;
use Webkul\Security\Filament\Resources\Roles\Tables\RolesTable;
use Filament\Resources\Resource;

class RoleResource extends Resource
{
    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
    }
}
