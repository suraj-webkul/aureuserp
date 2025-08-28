<x-dynamic-component
    :component="$getEntryWrapperView()"
    :entry="$entry"
>
    <div
        {{
            $attributes
                ->merge([
                    'id' => $getId(),
                ], escape: false)
                ->merge($getExtraAttributes(), escape: false)
        }}
    >
        @if (count($childComponentContainers = $getChildComponentContainers()))
            <x-filament::grid
                :default="$getGridColumns('default')"
                :sm="$getGridColumns('sm')"
                :md="$getGridColumns('md')"
                :lg="$getGridColumns('lg')"
                :xl="$getGridColumns('xl')"
                :two-xl="$getGridColumns('2xl')"
                class="gap-2"
            >
                @foreach ($childComponentContainers as $container)
                    <article
                        class="mb-4 rounded-xl bg-white/70 p-4 text-base shadow-sm transition-shadow hover:shadow-md dark:bg-gray-900/60"
                        @style([
                            'background-color: rgba(var(--primary-200), 0.1);' => $container->record->type == 'note',
                        ])
                    >
                        {{ $container }}
                    </article>
                @endforeach
            </x-filament::grid>
        @elseif (($placeholder = $getPlaceholder()) !== null)
            <x-filament-infolists::entries.placeholder>
                {{ $placeholder }}
            </x-filament-infolists::entries.placeholder>
        @endif
    </div>
</x-dynamic-component>
