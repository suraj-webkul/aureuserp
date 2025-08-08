<?php

namespace Webkul\Security\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasPermissionScope
{
    protected function getOwnerColumn(): string
    {
        return 'created_by';
    }

    protected function getAssignmentColumn(): ?string
    {
        return 'user_id';
    }

    public function scopeApplyPermissionScope(Builder $query): Builder
    {
        $user = filament()->auth()->user();

        if (! $user) {
            return $query;
        }

        $userIds = bouncer()->getAuthorizedUserIds();

        if (empty($userIds)) {
            return $query;
        }

        return $query->where(function (Builder $subQuery) use ($userIds) {
            $assignmentColumn = $this->getAssignmentColumn();

            $subQuery->whereIn($this->getOwnerColumn(), $userIds);

            if (
                $assignmentColumn &&
                $this->getConnection()->getSchemaBuilder()->hasColumn($this->getTable(), $assignmentColumn)
            ) {
                $subQuery->orWhereIn($assignmentColumn, $userIds);
            }
        });
    }
}
