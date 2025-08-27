<?php

namespace Webkul\Product\Filament\Resources\Categories;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Webkul\Blog\Filament\Admin\Clusters\Configurations\Resources\Categories\Tables\CategoryTable;
use Webkul\Product\Filament\Resources\Categories\Schemas\CategoryForm;
use Webkul\Product\Filament\Resources\Categories\Schemas\CategoryInfolist;
use Webkul\Product\Models\Category;
use BackedEnum;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoryTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CategoryInfolist::configure($schema);
    }
}
