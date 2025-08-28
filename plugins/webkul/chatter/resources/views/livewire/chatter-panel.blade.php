<div class="w-full h-full" wire:poll.10s="refresh">
    <!-- Toolbar -->
    <div class="sticky top-0 z-10 -mx-4 -mt-4 mb-4 bg-white/85 px-4 py-3 backdrop-blur supports-[backdrop-filter]:bg-white/60 dark:bg-gray-950/75">
        <div class="flex flex-col items-center gap-3">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <!-- Primary actions -->
                <div class="flex flex-wrap items-center gap-2">
                    @foreach (['messageAction', 'logAction', 'activityAction', 'fileAction', 'followerAction'] as $action)
                        @if ($this->{$action}->isVisible())
                            {{ $this->{$action} }}
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Filters (messages tab only) -->
            @if ($this->tab === 'messages')
                <div class="flex items-center justify-center w-full gap-2">
                    <div class="relative group">
                        <span class="absolute inset-y-0 flex items-center text-gray-400 pointer-events-none left-2 group-focus-within:text-primary-500">
                            <x-heroicon-m-magnifying-glass class="w-4 h-4" />
                        </span>
                        <input
                            type="text"
                            wire:model.debounce.300ms="search"
                            placeholder="Search messages..."
                            class="block w-64 py-2 pl-8 pr-3 text-sm text-gray-900 border-0 rounded-lg shadow-inner bg-gray-100/80 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-gray-900/60 dark:text-gray-100 dark:ring-gray-800"
                        />
                    </div>

                    <div>
                        <select
                            wire:model="filterType"
                            class="block w-40 px-2 py-2 text-sm text-gray-900 border-0 rounded-lg shadow-inner bg-gray-100/80 ring-1 ring-inset ring-gray-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-gray-900/60 dark:text-gray-100 dark:ring-gray-800"
                        >
                            <option value="all">All</option>
                            <option value="note">Notes</option>
                            <option value="comment">Comments</option>
                            <option value="notification">Notifications</option>
                            <option value="activity">Activities</option>
                        </select>
                    </div>

                    <label class="inline-flex items-center gap-2 px-3 py-2 text-xs text-gray-700 transition rounded-lg cursor-pointer select-none bg-gray-100/60 hover:bg-gray-100 dark:bg-gray-900/60 dark:text-gray-200 dark:hover:bg-gray-900/70">
                        <input type="checkbox" wire:model="pinnedOnly" class="border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:border-gray-700" />
                        <span>Pinned only</span>
                    </label>

                    @if ($this->hasFilters())
                        <div class="flex flex-wrap items-center gap-2 ms-auto">
                            @foreach($this->getActiveFilters() as $filter)
                                <button
                                    type="button"
                                    wire:click="removeFilter('{{ $filter['key'] }}')"
                                    class="inline-flex items-center gap-1 px-2 py-1 text-xs text-gray-700 rounded-full bg-gray-200/80 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-800/80"
                                >
                                    <span>{{ $filter['label'] }}</span>
                                    <x-heroicon-m-x-mark class="h-3.5 w-3.5" />
                                </button>
                            @endforeach

                            <button type="button" wire:click="clearAllFilters" class="text-xs text-primary-600 hover:underline dark:text-primary-400">
                                Clear all
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        @if ($this->tab === 'activities')
            <div wire:key="activities-{{ $this->refreshTick }}">
                {{ $this->activityInfolist }}
            </div>
        @else
            <div wire:key="messages-{{ $this->refreshTick }}">
                {{ $this->chatInfolist }}
            </div>
        @endif
    </div>

    <x-filament-actions::modals />
</div>
