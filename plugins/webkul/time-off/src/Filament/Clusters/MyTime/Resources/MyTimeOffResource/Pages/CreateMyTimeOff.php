<?php

namespace Webkul\TimeOff\Filament\Clusters\MyTime\Resources\MyTimeOffResource\Pages;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Webkul\Employee\Models\Employee;
use Webkul\TimeOff\Enums\State;
use Webkul\TimeOff\Filament\Clusters\MyTime\Resources\MyTimeOffResource;
use Webkul\TimeOff\Models\Leave;
use Webkul\TimeOff\Models\LeaveAllocation;
use Webkul\TimeOff\Models\LeaveType;

class CreateMyTimeOff extends CreateRecord
{
    protected static string $resource = MyTimeOffResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getCreatedNotification(): Notification
    {
        return Notification::make()
            ->success()
            ->title(__('time-off::filament/clusters/my-time/resources/my-time-off/pages/create-time-off.notification.success.title'))
            ->body(__('time-off::filament/clusters/my-time/resources/my-time-off/pages/create-time-off.notification.success.body'));
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        $employee = Employee::where('user_id', $user->id)->first();

        if (! $employee) {
            Notification::make()
                ->warning()
                ->title(__('time-off::filament/clusters/my-time/resources/my-time-off/pages/create-time-off.notification.warning.title'))
                ->body(__('time-off::filament/clusters/my-time/resources/my-time-off/pages/create-time-off.notification.warning.body'))
                ->send();

            $this->halt();

            return $data;
        }

        $data['employee_id'] = $employee->id;

        $data['department_id'] = $employee->department?->id;

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

        if (! empty($data['request_unit_half'])) {
            $data['duration_display'] = '0.5 day';

            $data['number_of_days'] = 0.5;
        } else {
            $startDate = Carbon::parse($data['request_date_from']);

            $endDate = $data['request_date_to'] ? Carbon::parse($data['request_date_to']) : $startDate;
            $days = $startDate->diffInDays($endDate) + 1;

            $data['duration_display'] = "{$days} day(s)";
            $data['number_of_days'] = $days;
            $data['date_to'] = $data['request_date_to'];
        }

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

        $data['creator_id'] = $user->id;

        $data['state'] = State::CONFIRM->value;

        $data['date_from'] = $data['request_date_from'];

        return $data;
    }
}
