<?php

namespace Webkul\TimeOff\Filament\Clusters\MyTime\Resources\MyTimeOffResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Webkul\Chatter\Filament\Actions as ChatterActions;
use Webkul\TimeOff\Filament\Clusters\MyTime\Resources\MyTimeOffResource;
use Webkul\TimeOff\Traits\TimeOffHelper;

class EditMyTimeOff extends EditRecord
{
    use TimeOffHelper;

    protected static string $resource = MyTimeOffResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('time-off::filament/clusters/my-time/resources/my-time-off/pages/edit-time-off.notification.title'))
            ->body(__('time-off::filament/clusters/my-time/resources/my-time-off/pages/edit-time-off.notification.body'));
    }

    protected function getHeaderActions(): array
    {
        return [
            ChatterActions\ChatterAction::make()
                ->setResource(static::$resource),
            ViewAction::make(),
            DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title(__('time-off::filament/clusters/my-time/resources/my-time-off/pages/edit-time-off.header-actions.delete.notification.title'))
                        ->body(__('time-off::filament/clusters/my-time/resources/my-time-off/pages/edit-time-off.header-actions.delete.notification.body'))
                ),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->record;

        if ($record && ! MyTimeOffResource::isEditableState($record)) {
            Notification::make()
                ->title(__('time-off::filament/clusters/my-time/resources/my-time-off/pages/edit-time-off.notification.action_not_allowed.title'))
                ->body(__('time-off::filament/clusters/my-time/resources/my-time-off/pages/edit-time-off.notification.action_not_allowed.body'))
                ->danger()
                ->send();
            $this->redirect(route('filament.admin.time-off.dashboard.resources.my-time-offs.view', $record));
            $this->halt();

            return $record->toArray();
        }
        $user = Auth::user();

        $employee = $user->employee;

        if ($employee) {
            $data['employee_id'] = $employee->id;
            $data['department_id'] = $employee->department?->id;
        }

        if (isset($data['employee_id'])) {
            if ($employee->calendar) {
                $data['calendar_id'] = $employee->calendar->id;
                $data['number_of_hours'] = $employee->calendar->hours_per_day;
            }

            $user = $employee?->user;

            if ($user) {
                $data['user_id'] = $user->id;

                $data['company_id'] = $user->default_company_id;

                $data['employee_company_id'] = $user->default_company_id;
            }
        }

        return $this->mutateTimeOffData($data, $this->record?->id);
    }
}
