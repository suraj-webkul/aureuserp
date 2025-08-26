<?php

namespace Webkul\Partner\Filament\Resources\Tag;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Webkul\Partner\Models\Tag;
use Webkul\Partners\Filament\Resources\Tag\Schemas\TagForm;
use Webkul\Partners\Filament\Resources\Tag\Tables\TagTable;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationLabel(): string
    {
        return __('partners::filament/resources/tag.navigation.title');
    }

    public static function form(Schema $schema): Schema
    {
        return TagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TagTable::configure($table);
    }
}
