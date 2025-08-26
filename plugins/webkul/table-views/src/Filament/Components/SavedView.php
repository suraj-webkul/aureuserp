<?php

namespace Webkul\TableViews\Filament\Components;

use Closure;
use Webkul\TableViews\Models\TableView;
use Illuminate\Database\Eloquent\Model;
class SavedView extends PresetView
{
    protected Model|array|string|Closure|null $model;

  

    public function getModels(): TableView
    {
        return $this->model;
    }

    public function isFavorite(string|int|null $id = null): bool
    {
        $tableViewFavorite = $this->getCachedFavoriteTableViews()
            ->where('view_type', 'saved')
            ->where('view_key', $id ?? $this->model->id)
            ->first();

        return (bool) ($tableViewFavorite?->is_favorite ?? $this->isFavorite);
    }

    public function isPublic(): bool
    {
        return $this->model->is_public;
    }

    public function isEditable(): bool
    {
        return $this->model->user_id === auth()->id();
    }

    public function isReplaceable(): bool
    {
        return $this->model->user_id === auth()->id();
    }

    public function isDeletable(): bool
    {
        return $this->model->user_id === auth()->id();
    }

    public function getVisibilityIcon(): string
    {
        return $this->isPublic() ? 'heroicon-o-eye' : 'heroicon-o-user';
    }
}
