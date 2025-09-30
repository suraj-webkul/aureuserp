<x-filament-panels::page>
    {{-- Record Navigation Tabs - with proper styling --}}
    @if($recordTabs = $this->getRecordTabs())
        <div class="fi-tabs mb-6">
            <nav class="flex gap-1 overflow-x-auto">
                @foreach($recordTabs as $tab)
                    <a
                        href="{{ $tab->getUrl() }}"
                        @class([
                            'fi-tabs-item',
                            'fi-active ' => $tab->isActive(),
                        ])
                    >
                        @if($tab->getIcon())
                            <x-filament::icon
                                :icon="$tab->getIcon()"
                                class="w-5 h-5"
                            />
                        @endif
                        <span>{{ $tab->getLabel() }}</span>
                    </a>
                @endforeach
            </nav>
        </div>
    @endif

    {{-- Original Edit Form --}}
    <form wire:submit="save">
        {{ $this->form }}

        <x-filament-actions::modals />
        
        <div class="flex flex-wrap items-center gap-4 justify-start mt-6">
            @foreach($this->getFormActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    </form>
</x-filament-panels::page>