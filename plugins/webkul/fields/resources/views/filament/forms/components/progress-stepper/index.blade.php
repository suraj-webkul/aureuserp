@php
    $gridDirection = $getGridDirection() ?? 'column';
    $hasInlineLabel = $hasInlineLabel();
    $id = $getId();
    $isDisabled = $isDisabled();
    $isInline = $isInline();
    $isMultiple = $isMultiple();
    $statePath = $getStatePath();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
    :has-inline-label="$hasInlineLabel"
>
    <x-slot
        name="label"
        @class([
            'sm:pt-1.5' => $hasInlineLabel,
        ])
    >
        {{ $getLabel() }}
    </x-slot>

    <div
        {{
            \Filament\Support\prepare_inherited_attributes($attributes)
                ->merge($getExtraAttributes(), escape: false)
                ->merge([
                    'id' => $getId(),
                ], escape: false)
                ->class([
                    'state-container justify-end',
                    'fi-fo-radio',
                    '-mt-3' => (! $isInline) && ($gridDirection === 'column'),
                    'flex flex-wrap' => $isInline,
                    'grid' => ! $isInline,
                    'grid-cols-' . $getColumns('default') => ! $isInline && $getColumns('default'),
                    'sm:grid-cols-' . $getColumns('sm') => ! $isInline && $getColumns('sm'),
                    'md:grid-cols-' . $getColumns('md') => ! $isInline && $getColumns('md'),
                    'lg:grid-cols-' . $getColumns('lg') => ! $isInline && $getColumns('lg'),
                    'xl:grid-cols-' . $getColumns('xl') => ! $isInline && $getColumns('xl'),
                    '2xl:grid-cols-' . $getColumns('2xl') => ! $isInline && $getColumns('2xl'),
                    'grid-flow-row' => ! $isInline && $gridDirection === 'row',
                    'grid-flow-col' => ! $isInline && $gridDirection === 'column',
                ])
        }}
    >
        {{ $getChildComponentContainer() }}

        @foreach ($getOptions() as $value => $label)
            @php
                $inputId = "{$id}-{$value}";
                $shouldOptionBeDisabled = $isDisabled || $isOptionDisabled($value, $label);
            @endphp

            <div
                @class([
                    'state' => true,
                    'border-primary-500',
                    'break-inside-avoid pt-3' => (! $isInline) && ($gridDirection === 'column'),
                ])
            >
                <input
                    @disabled($shouldOptionBeDisabled)
                    id="{{ $inputId }}"
                    @if (! $isMultiple)
                        name="{{ $id }}"
                    @endif
                    type="{{ $isMultiple ? 'checkbox' : 'radio' }}"
                    value="{{ $value }}"
                    wire:loading.attr="disabled"
                    {{ $applyStateBindingModifiers('wire:model') }}="{{ $statePath }}"
                    {{ $getExtraInputAttributeBag()->class(['peer pointer-events-none absolute opacity-0']) }}
                />

                <x-filament::button
                    class="stage-button"
                    :color="$getColor($value)"
                    {{-- :disabled="$shouldOptionBeDisabled" --}}
                    :for="$inputId"
                    :icon="$getIcon($value)"
                    tag="label"
                >
                    {{ $label }}
                </x-filament::button>
            </div>
        @endforeach
    </div>
</x-dynamic-component>

@pushOnce('styles')
    <style>
        .stage-button {
            border-radius: 0 !important;
            padding-left: 30px !important;
            padding-right: 20px !important;
            border: 1px solid rgba(var(--gray-950), 0.2) !important;
            box-shadow: none !important;
        }

        .dark .stage-button {
            border: 1px solid hsla(0, 0%, 100%, .2) !important;
        }

        .stage-button:after {
            content: "" !important;
            position: absolute !important;
            top: 50% !important;
            right: -14px !important;
            width: 26px !important;
            height: 26px !important;
            z-index: 1 !important;
            transform: translateY(-50%) rotate(45deg) !important;
            background-color: #ffffff !important;
            border-right: 1px solid rgba(var(--gray-950), 0.3) !important;
            border-top: 1px solid rgba(var(--gray-950), 0.3) !important;
            transition-duration: 75ms !important;
        }

        .dark .stage-button:after {
            background-color: rgba(var(--gray-900),var(--tw-bg-opacity)) !important;
            border-right: 1px solid hsla(0, 0%, 100%, .2) !important;
            border-top: 1px solid hsla(0, 0%, 100%, .2) !important;
        }

        .dark .stage-button:hover:after {
            background-color: rgba(var(--gray-800),var(--tw-bg-opacity)) !important;
        }

        .state-container .state:last-child .stage-button {
            border-radius: 0 8px 8px 0 !important;
        }

        .state-container .state:first-child .stage-button {
            border-radius: 8px 0 0 8px !important;
        }

        .state-container .state:last-child .stage-button:after {
            content: none !important;
        }

        input:checked + .stage-button {
            color: #fff !important;
            border: 1px solid rgba(var(--c-500), var(--tw-bg-opacity)) !important;
        }

        input:checked + .stage-button:after {
            background-color: rgba(var(--c-600), var(--tw-bg-opacity)) !important;
            border-right: 1px solid rgba(var(--c-500)) !important;
            border-top: 1px solid rgba(var(--c-500)) !important;
        }

        .dark input:checked + .stage-button:after {
            background-color: rgba(var(--c-500), var(--tw-bg-opacity)) !important;
        }

        input:checked + .stage-button:hover:after {
            background-color: rgba(var(--c-500), var(--tw-bg-opacity)) !important;
            transition-duration: 75ms !important;
        }

        .dark input:checked + .stage-button:hover:after {
            background-color: rgba(var(--c-400), var(--tw-bg-opacity)) !important;
        }
    </style>
@endPushOnce
