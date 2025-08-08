<?php

namespace Webkul\Security\Traits;

use Illuminate\Database\Eloquent\Builder;
use Webkul\Security\Enums\PermissionType;

trait HasPermissionScope
{
    public function scopeApplyPermissionScope(Builder $query): Builder
    {
        $user = filament()->auth()->user();

        if (! $user) {
            return $query;
        }

        $userIds = bouncer()->getAuthorizedUserIds();

        if ($userIds === null) {
            return $query;
        }

        return $query->where(function (Builder $subQuery) use ($userIds, $user) {
            $subQuery->whereIn($this->getOwnerColumn(), $userIds);

            if ($user->resource_permission === PermissionType::INDIVIDUAL) {
                $assignmentColumn = $this->getAssignmentColumn();

                if (
                    $assignmentColumn
                    && $this->getConnection()->getSchemaBuilder()->hasColumn($this->getTable(), $assignmentColumn)
                ) {
                    $subQuery->orWhere($assignmentColumn, $user->id);
                }
            }
        });
    }

    protected function getOwnerColumn(): string
    {
        return 'created_by';
    }

    protected function getAssignmentColumn(): ?string
    {
        return 'user_id';
    }
}
