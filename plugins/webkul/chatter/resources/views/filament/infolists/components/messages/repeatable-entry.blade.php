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
                        class="mb-3 rounded-lg border border-gray-200 bg-white p-6 text-base dark:border-gray-700 dark:bg-gray-900"
                        @style([
                            'background-color: var(--color-200);' => data_get($container->getRecord(), 'type') === 'note',
                        ])
                    >
                        {{ $container }}
                    </article>
                @endforeach
            </div>
        @elseif (($placeholder = $getPlaceholder()) !== null)
            <div class="fi-in-placeholder text-sm leading-6 text-gray-400 dark:text-gray-500">
                {{ $placeholder }}
            </div>
        @endif
    </div>
</x-dynamic-component>
