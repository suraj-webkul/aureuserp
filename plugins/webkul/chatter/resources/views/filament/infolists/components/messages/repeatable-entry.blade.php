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
            <div
                {{
                    \Filament\Support\prepare_inherited_attributes($attributes)
                        ->merge($getExtraAttributes(), escape: false)
                        ->class([
                            'gap-2',
                        ])
                }}
            >
                @foreach ($childComponentContainers as $container)
                    <article
                        @class([
                            'mb-4 rounded-xl p-4 text-base shadow-sm ring-1 transition-shadow hover:shadow-md',
                            'bg-gray-50 ring-gray-200 dark:bg-gray-800/50 dark:ring-gray-800' => data_get($container->getRecord(), 'type') === 'note',
                            'bg-white/70 ring-black/5 dark:bg-gray-900/60 dark:ring-white/5' => data_get($container->getRecord(), 'type') !== 'note',
                        ])
                    >
                        {{ $container }}
                    </article>
                @endforeach
            </div>
        @elseif (($placeholder = $getPlaceholder()) !== null)
            <div class="text-sm leading-6 text-gray-400 fi-in-placeholder dark:text-gray-500">
                {{ $placeholder }}
            </div>
        @endif
    </div>
</x-dynamic-component>
