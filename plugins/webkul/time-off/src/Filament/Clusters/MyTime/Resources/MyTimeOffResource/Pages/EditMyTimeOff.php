<?php

namespace Webkul\TimeOff\Filament\Clusters\MyTime\Resources\MyTimeOffResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Webkul\Chatter\Filament\Actions as ChatterActions;
use Webkul\TimeOff\Enums\State;
use Webkul\TimeOff\Filament\Clusters\MyTime\Resources\MyTimeOffResource;
use Webkul\TimeOff\Models\Leave;
use Webkul\TimeOff\Models\LeaveAllocation;
use Webkul\TimeOff\Models\LeaveType;

class EditMyTimeOff extends EditRecord
{
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

        if (isset($data['request_unit_half'])) {
            $data['duration_display'] = '0.5 day';

            $data['number_of_days'] = 0.5;
        } else {
            $startDate = Carbon::parse($data['request_date_from']);
            $endDate = isset($data['request_date_to']) ? Carbon::parse($data['request_date_to']) : $startDate;

            $data['duration_display'] = $startDate->diffInDays($endDate) + 1 .' day(s)';

            $data['number_of_days'] = $startDate->diffInDays($endDate) + 1;
        }

        $data['date_from'] = $data['request_date_from'];
        $data['date_to'] = isset($data['request_date_to']) ? $data['request_date_to'] : null;
        $requestedDays = $data['number_of_days'];
        $leaveTypeId = $data['holiday_status_id'] ?? null;

        if ($leaveTypeId) {
            $leaveType = LeaveType::find($leaveTypeId);

            if ($leaveType && $leaveType->requires_allocation) {
                $endDate = Carbon::now()->endOfYear();

                $totalAllocated = LeaveAllocation::where('employee_id', $employee->id)
                    ->where('holiday_status_id', $leaveTypeId)
                    ->where(function ($query) use ($endDate) {
                        $query->where('date_to', '<=', $endDate)
                            ->orWhereNull('date_to');
                    })
                    ->sum('number_of_days');

                $totalTaken = Leave::where('employee_id', $employee->id)
                    ->where('holiday_status_id', $leaveTypeId)
                    ->where('state', '!=', State::REFUSE->value)
                    ->where('id', '!=', $record->id)
                    ->sum('number_of_days');

                $availableBalance = round($totalAllocated - $totalTaken, 1);

                if ($totalAllocated <= 0) {
                    Notification::make()
                        ->danger()
                        ->title(__('time-off::filament/clusters/my-time/resources/my-time-off/pages/create-time-off.notification.leave_request_denied_no_allocation.title'))
                        ->body(__('time-off::filament/clusters/my-time/resources/my-time-off/pages/create-time-off.notification.leave_request_denied_no_allocation.body', ['leaveType' => $leaveType->name]))
                        ->send();

                    $this->halt();

                    return $data;
                }

                if ($requestedDays > $availableBalance) {
                    Notification::make()
                        ->danger()
                        ->title(__('time-off::filament/clusters/my-time/resources/my-time-off/pages/create-time-off.notification.leave_request_denied_insufficient_balance.title'))
                        ->body(__('time-off::filament/clusters/my-time/resources/my-time-off/pages/create-time-off.notification.leave_request_denied_insufficient_balance.body', [
                            'available_balance' => $availableBalance,
                            'requested_days'    => $requestedDays,
                        ]))

                        ->send();

                    $this->halt();

                    return $data;
                }
            }
        }

        $overlap = $this->checkForOverlappingLeave(
            $employee->id,
            $data['request_date_from'],
            $data['request_date_to'] ?? $data['request_date_from'],
            $this->record?->id
        );

        if ($overlap) {

            Notification::make()
                ->title(__('time-off::filament/clusters/my-time/resources/my-time-off/pages/edit-time-off.notification.overlap.title'))
                ->body(__('time-off::filament/clusters/my-time/resources/my-time-off/pages/edit-time-off.notification.overlap.body'))
                ->danger()
                ->send();
            $this->halt();

            return $data;
        }

        return $data;
    }

    protected function checkForOverlappingLeave(int $employeeId, string $startDate, ?string $endDate, ?int $excludeRecordId = null): bool
    {
        $start = Carbon::parse($startDate);
        $end = $endDate ? Carbon::parse($endDate) : $start;

        $query = Leave::where('employee_id', $employeeId)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('date_from', [$start, $end])
                    ->orWhereBetween('date_to', [$start, $end])
                    ->orWhere(function ($query) use ($start, $end) {
                        $query->where('date_from', '<=', $start)
                            ->where('date_to', '>=', $end);
                    });
            });

        if ($excludeRecordId) {
            $query->where('id', '!=', $excludeRecordId);
        }

        return $query->exists();
    }
}
