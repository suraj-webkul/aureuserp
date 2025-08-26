<?php

namespace Webkul\Employee\Filament\Clusters\Configurations\Resources\ActivityPlan;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Webkul\Employee\Filament\Clusters\Configurations;
use Webkul\Employee\Filament\Clusters\Configurations\Resources\ActivityPlan\Pages\EditActivityPlan;
use Webkul\Employee\Filament\Clusters\Configurations\Resources\ActivityPlan\Pages\ListActivityPlans;
use Webkul\Employee\Filament\Clusters\Configurations\Resources\ActivityPlan\Pages\ViewActivityPlan;
use Webkul\Employee\Filament\Clusters\Configurations\Resources\ActivityPlan\RelationManagers\ActivityTemplateRelationManager;
use Webkul\Employee\Models\ActivityPlan;
use Webkul\Employees\Filament\Clusters\Configurations\Resources\ActivityPlan\Schemas\ActivityPlanForm;
use Webkul\Employees\Filament\Clusters\Configurations\Resources\ActivityPlan\Schemas\ActivityPlanInfolist;
use Webkul\Employees\Filament\Clusters\Configurations\Resources\ActivityPlan\Schemas\ActivityPlanTable;

class ActivityPlanResource extends Resource
{
    protected static ?string $model = ActivityPlan::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $cluster = Configurations::class;

    public static function getNavigationLabel(): string
    {
        return __('employees::filament/clusters/configurations/resources/activity-plan.navigation.title');
    }

    public static function form(Schema $schema): Schema
    {
        return ActivityPlanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivityPlanTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ActivityPlanInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            ActivityTemplateRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityPlans::route('/'),
            'view' => ViewActivityPlan::route('/{record}'),
            'edit' => EditActivityPlan::route('/{record}/edit'),
        ];
    }
}
