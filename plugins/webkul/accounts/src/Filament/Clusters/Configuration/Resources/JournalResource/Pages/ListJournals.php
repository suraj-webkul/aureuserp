<?php

namespace Webkul\Account\Filament\Clusters\Configuration\Resources\JournalResource\Pages;

use Webkul\Account\Filament\Clusters\Configuration\Resources\JournalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJournals extends ListRecords
{
    protected static string $resource = JournalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
