<?php

namespace Webkul\Security\Filament\Resources\Roles;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Webkul\Security\Filament\Resources\Roles\Schemas\RoleForm;
use Webkul\Security\Filament\Resources\Roles\Tables\RolesTable;
use Webkul\Security\Filament\Resources\RoleResource\Pages\CreateRole;
use Webkul\Security\Filament\Resources\RoleResource\Pages\EditRole;
use Webkul\Security\Filament\Resources\RoleResource\Pages\ListRoles;
use Webkul\Security\Filament\Resources\RoleResource\Pages\ViewRole;
use BezhanSalleh\FilamentShield\Resources\RoleResource as BaseRoleResource;

class RoleResource extends BaseRoleResource
{
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'view'   => ViewRole::route('/{record}'),
            'edit'   => EditRole::route('/{record}/edit'),
        ];
    }
}
