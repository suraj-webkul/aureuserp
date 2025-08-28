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
                @php $lastDateLabel = null; @endphp
                @foreach ($childComponentContainers as $container)
                    @php
                        $createdAt = data_get($container->getRecord(), 'created_at');
                        try {
                            $dt = $createdAt instanceof \Carbon\CarbonInterface ? $createdAt : \Carbon\Carbon::parse($createdAt);
                        } catch (\Throwable $e) {
                            $dt = null;
                        }
                        $currentLabel = '';
                        if ($dt) {
                            if ($dt->isToday()) {
                                $currentLabel = __('Today');
                            } elseif ($dt->isYesterday()) {
                                $currentLabel = __('Yesterday');
                            } else {
                                $currentLabel = $dt->format('M j, Y');
                            }
                        }
                    @endphp

                    @if ($currentLabel && $currentLabel !== $lastDateLabel)
                        <div class="relative my-6">
                            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                <div class="w-full border-t border-gray-200 dark:border-gray-800"></div>
                            </div>
                            <div class="relative flex justify-center">
                                <span class="px-3 text-xs font-medium text-gray-500 bg-white/90 dark:bg-gray-950/75 dark:text-gray-400">
                                    {{ $currentLabel }}
                                </span>
                            </div>
                        </div>
                        @php $lastDateLabel = $currentLabel; @endphp
                    @endif
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
