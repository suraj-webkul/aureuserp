<?php

namespace Webkul\Security\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasPermissionScope
{
    protected ?string $ownerColumn = 'creator_id';

    protected ?string $assignmentColumn = 'user_id';

    protected function getOwnerColumn(): string
    {
        return $this->ownerColumn;
    }

    protected function getAssignmentColumn(): ?string
    {
        return $this->assignmentColumn;
    }

    protected function setOwnerColumn(string $column): void
    {
        $this->ownerColumn = $column;
    }

    protected function setAssignmentColumn(?string $column): void
    {
        $this->assignmentColumn = $column;
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
