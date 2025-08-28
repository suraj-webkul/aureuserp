<?php

namespace Webkul\Chatter\Filament\Actions\Chatter;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;

class FiltersAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'filters.action';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->hiddenLabel()
            ->icon('heroicon-o-funnel')
            ->color('gray')
            ->outlined()
            ->slideOver(false)
            ->tooltip(__('Filters'))
            ->mountUsing(function ($livewire, $arguments, $form, $schema) {
                $schema->fill([
                    'search'     => $livewire->search ?? '',
                    'filterType' => $livewire->filterType ?? 'all',
                    'dateRange'  => $livewire->dateRange ?? null,
                    'sortBy'     => $livewire->sortBy ?? 'created_at_desc',
                    'pinnedOnly' => (bool) ($livewire->pinnedOnly ?? false),
                ]);
            })
            ->schema([
                Group::make()
                    ->schema([
                        TextInput::make('search')
                            ->label(__('Search'))
                            ->placeholder(__('Search messages...')),
                        Select::make('filterType')
                            ->label(__('Type'))
                            ->options([
                                'all'          => __('All types'),
                                'note'         => __('Notes'),
                                'comment'      => __('Comments'),
                                'notification' => __('Notifications'),
                                'activity'     => __('Activities'),
                            ])
                            ->native(false),
                        Select::make('dateRange')
                            ->label(__('Date'))
                            ->options([
                                ''          => __('Any time'),
                                'today'     => __('Today'),
                                'yesterday' => __('Yesterday'),
                                'week'      => __('Last 7 days'),
                                'month'     => __('Last 30 days'),
                                'quarter'   => __('Last 3 months'),
                                'year'      => __('Last year'),
                            ])
                            ->native(false),
                        Select::make('sortBy')
                            ->label(__('Sort by'))
                            ->options([
                                'created_at_desc' => __('Newest first'),
                                'created_at_asc'  => __('Oldest first'),
                                'updated_at_desc' => __('Recently updated'),
                                'priority'        => __('Priority'),
                            ])
                            ->native(false),
                        Toggle::make('pinnedOnly')
                            ->label(__('Pinned only')),
                    ])
                    ->columns(2),
            ])
            ->modalSubmitAction(function ($action) {
                $action->label(__('Apply filters'))->icon('heroicon-m-check');
            })
            ->action(function (array $data, $livewire) {
                $livewire->search = (string) ($data['search'] ?? '');
                $livewire->filterType = (string) ($data['filterType'] ?? 'all');
                $livewire->dateRange = $data['dateRange'] !== '' ? ($data['dateRange'] ?? null) : null;
                $livewire->sortBy = (string) ($data['sortBy'] ?? 'created_at_desc');
                $livewire->pinnedOnly = (bool) ($data['pinnedOnly'] ?? false);

                if (method_exists($livewire, 'dispatch')) {
                    $livewire->dispatch('chatter.refresh');
                }
            });
    }
}
