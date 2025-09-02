<?php

namespace Webkul\Blog\Filament\Admin\Clusters\Configurations\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;

class CategoryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('blogs::filament/admin/clusters/configurations/resources/category.table.columns.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('blogs::filament/admin/clusters/configurations/resources/category.table.columns.created-at'))
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->hidden(fn($record) => $record->trashed())
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('blogs::filament/admin/clusters/configurations/resources/category.table.actions.edit.notification.title'))
                            ->body(__('blogs::filament/admin/clusters/configurations/resources/category.table.actions.edit.notification.body')),
                    ),
                RestoreAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('blogs::filament/admin/clusters/configurations/resources/category.table.actions.restore.notification.title'))
                            ->body(__('blogs::filament/admin/clusters/configurations/resources/category.table.actions.restore.notification.body')),
                    ),
                DeleteAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('blogs::filament/admin/clusters/configurations/resources/category.table.actions.delete.notification.title'))
                            ->body(__('blogs::filament/admin/clusters/configurations/resources/category.table.actions.delete.notification.body')),
                    ),
                ForceDeleteAction::make()
                    ->action(function ($record, $action) {
                        try {
                            if ($record->posts()->exists()) {
                                $action->failure(); // Prevent success notification
                                Notification::make()
                                    ->danger()
                                    ->title(__('blogs::filament/admin/clusters/configurations/resources/category.table.actions.force-delete-error.notification.title'))
                                    ->body(__('blogs::filament/admin/clusters/configurations/resources/category.table.actions.force-delete-error.notification.body'))
                                    ->send();
                                return; // Stop further execution, do not delete
                            }
                            $record->forceDelete();
                        } catch (QueryException $e) {
                            $action->failure();
                            Notification::make()
                                ->danger()
                                ->title(__('blogs::filament/admin/clusters/configurations/resources/category.table.actions.force-delete-error.notification.title'))
                                ->body(__('blogs::filament/admin/clusters/configurations/resources/category.table.actions.force-delete-error.notification.body'))
                                ->send();
                        }
                    })
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('blogs::filament/admin/clusters/configurations/resources/category.table.actions.force-delete.notification.title'))
                            ->body(__('blogs::filament/admin/clusters/configurations/resources/category.table.actions.force-delete.notification.body')),
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    RestoreBulkAction::make()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title(__('blogs::filament/admin/clusters/configurations/resources/category.table.bulk-actions.restore.notification.title'))
                                ->body(__('blogs::filament/admin/clusters/configurations/resources/category.table.bulk-actions.restore.notification.body')),
                        ),
                    DeleteBulkAction::make()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title(__('blogs::filament/admin/clusters/configurations/resources/category.table.bulk-actions.delete.notification.title'))
                                ->body(__('blogs::filament/admin/clusters/configurations/resources/category.table.bulk-actions.delete.notification.body')),
                        ),
                    ForceDeleteBulkAction::make()
                        ->action(function (Collection $records, $action) {
                            $hasError = false;

                            foreach ($records as $record) {
                                if ($record->posts()->exists()) {
                                    $hasError = true;
                                } else {
                                    $record->forceDelete();
                                }
                            }

                            if ($hasError) {
                                $action->failure(); // triggers failureNotification automatically
                            } else {
                                $action->success(); // triggers successNotification automatically
                            }
                        })
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title(__('blogs::filament/admin/clusters/configurations/resources/category.table.bulk-actions.force-delete.notification.title'))
                                ->body(__('blogs::filament/admin/clusters/configurations/resources/category.table.bulk-actions.force-delete.notification.body')),
                        )
                        ->failureNotification(
                            Notification::make()
                                ->danger()
                                ->title(__('blogs::filament/admin/clusters/configurations/resources/category.table.bulk-actions.force-delete-error.notification.title'))
                                ->body(__('blogs::filament/admin/clusters/configurations/resources/category.table.bulk-actions.force-delete-error.notification.body')),
                        )
                ]),
            ]);
    }
}
