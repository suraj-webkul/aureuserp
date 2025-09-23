<?php

namespace Webkul\TimeOff\Traits;

use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Webkul\Employee\Models\Employee;
use Webkul\TimeOff\Enums\State;
use Webkul\TimeOff\Models\Leave;
use Webkul\TimeOff\Models\LeaveAllocation;
use Webkul\TimeOff\Models\LeaveType;

trait TimeOffHelper
{
    public function mutateTimeOffData(array $data, ?int $excludeRecordId = null, $action = null): array
    {
        $this->calculateBusinessDays($data);

        $this->handleLeaveOverlap($data, $excludeRecordId, $action);

        $this->handleLeaveAllocation($data, $action);

        $data['creator_id'] = Auth::user()->id;
        $data['state'] = State::CONFIRM->value;
        $data['date_from'] = $data['request_date_from'] ?? null;
        $data['date_to'] = $data['request_date_to'] ?? null;

        return $data;
    }

    /**
     * Calculate business days between start and end dates
     */
    private function calculateBusinessDays(array &$data): void
    {
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
    }

    /**
     * Check if leave overlaps with another leave
     */
    private function handleLeaveOverlap(array &$data, ?int $excludeRecordId, $action): void
    {
        $employee = Employee::find($data['employee_id']);
        $overlap = $this->checkForOverlappingLeave(
            $employee->id,
            $data['request_date_from'],
            $data['request_date_to'] ?? $data['request_date_from'],
            $excludeRecordId
        );

        if ($overlap) {
            Notification::make()
                ->danger()
                ->title(__('time-off::filament/clusters/my-time/resources/my-time-off/pages/create-time-off.notification.overlap.title'))
                ->body(__('time-off::filament/clusters/my-time/resources/my-time-off/pages/create-time-off.notification.overlap.body'))
                ->send();
            if ($action) {
                $action->halt();
            } else {
                $this->halt();
            }
        }
    }

    /**
     * Check if leave allocation is sufficient
     */
    private function handleLeaveAllocation(array &$data, $action): void
    {
        $employee = Employee::find($data['employee_id']);
        $leaveTypeId = $data['holiday_status_id'] ?? null;

        if ($leaveTypeId) {
            $leaveType = LeaveType::find($leaveTypeId);

            if ($leaveType && $leaveType->requires_allocation) {
                $this->checkLeaveBalance($employee, $data, $leaveTypeId, $leaveType, $action);
            }
        }
    }

    /**
     * Check if leave balance is sufficient
     */
    private function checkLeaveBalance(Employee $employee, array &$data, int $leaveTypeId, $leaveType, $action): void
    {
        $requestedDays = $data['number_of_days'];

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
            if ($action) {
                $action->halt();
            } else {
                $this->halt();
            }
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
            if ($action) {
                $action->halt();
            } else {
                $this->halt();
            }
        }
    }

    /**
     * Check for overlapping leave requests
     */
    private function checkForOverlappingLeave(int $employeeId, string $startDate, ?string $endDate, ?int $excludeRecordId = null): bool
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
