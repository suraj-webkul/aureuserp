@php
    $record = $getRecord();
    $changes = is_array($record->properties) ? $record->properties : [];
@endphp

<x-dynamic-component
    :component="$getEntryWrapperView()"
    :entry="$entry"
>
    <div {{ $attributes->merge($getExtraAttributes())->class('') }}>
        @switch($record->type)
            @case('note')
            @case('comment')
                @if ($record->subject)
                    <div class="mb-2 text-sm font-semibold text-gray-800 dark:text-gray-200">
                        <span class="block text-gray-500 dark:text-gray-400">
                            @lang('chatter::views/filament/infolists/components/messages/content-text-entry.subject'):
                        </span>
                        {{ $record->subject }}
                    </div>
                @endif

                @if($record->body)
                    <div class="text-sm">
                        {!! $record->body !!}
                    </div>
                @endif

                <section class="mt-3 text-gray-700">
                    <div class="px-0">
                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($record->attachments->chunk(6) as $chunk)
                                @foreach($chunk as $attachment)
                                        @php
                                            $fileExtension = strtolower(pathinfo($attachment->original_file_name, PATHINFO_EXTENSION));

                                            switch($fileExtension) {
                                                case 'pdf':
                                                    $icon = 'heroicon-o-document-text';
                                                    break;
                                                case 'sql':
                                                    $icon = 'heroicon-o-database';
                                                    break;
                                                case 'csv':
                                                    $icon = 'heroicon-o-table-cells';
                                                    break;
                                                case 'md':
                                                    $icon = 'heroicon-o-document';
                                                    break;
                                                default:
                                                    $icon = 'heroicon-o-document';
                                            }
                                        @endphp

                                        <div class="flex items-center gap-3 rounded-lg bg-white/60 px-3 py-2 shadow-sm ring-1 ring-black/5 dark:bg-gray-900/50 dark:ring-white/5">
                                            <div class="flex h-9 w-9 items-center justify-center rounded-md bg-white shadow-sm ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10">
                                                <x-filament::icon
                                                    :icon="$icon"
                                                    class="h-5 w-5"
                                                />
                                            </div>

                                            <div class="flex flex-col gap-1">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $attachment->original_file_name }}
                                                    </span>
                                                </div>

                                                <div class="flex items-center gap-1.5">
                                                    @if(in_array($fileExtension, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']))
                                                        <x-filament::button
                                                            size="xs"
                                                            color="gray"
                                                            icon="heroicon-m-eye"
                                                            class="!gap-0"
                                                            icon-only
                                                            tag="a"
                                                            :href="Storage::url($attachment->file_path)"
                                                            target="_blank"
                                                            :tooltip="__('chatter::views/filament/infolists/components/messages/content-text-entry.preview')"
                                                        />
                                                    @endif

                                                    <x-filament::button
                                                        size="xs"
                                                        color="gray"
                                                        icon="heroicon-m-arrow-down-tray"
                                                        class="!gap-0"
                                                        icon-only
                                                        tag="a"
                                                        :href="Storage::url($attachment->file_path)"
                                                        download="{{ $attachment->original_file_name }}"
                                                        :tooltip="__('chatter::views/filament/infolists/components/messages/content-text-entry.download')"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </section>

                @break
            @case('notification')
                @if ($record->body)
                    <h3 class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                        {!! $record->body !!}
                    </h3>
                @endif

                @if (
                    count($changes) > 0
                    && $record->event !== 'created'
                )
                    <div class="mt-2 bg-white rounded-lg shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-2">
                                <x-heroicon-m-arrow-path class="w-5 h-5 text-primary-500"/>

                                <h3 class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                    @lang('chatter::views/filament/infolists/components/messages/content-text-entry.changes-made')
                                </h3>
                            </div>
                        </div>

                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($changes as $field => $change)
                                @if(is_array($change))
                                    <div class="p-4">
                                        <div class="flex items-center gap-2 mb-3">
                                            @if($field === 'title')
                                                <x-heroicon-m-pencil-square class="w-4 h-4 text-gray-500"/>
                                            @elseif($field === 'due_date')
                                                <x-heroicon-m-calendar class="w-4 h-4 text-gray-500"/>
                                            @else
                                                <x-heroicon-m-arrow-path class="w-4 h-4 text-gray-500"/>
                                            @endif

                                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                                @lang('chatter::views/filament/infolists/components/messages/content-text-entry.modified', [
                                                    'field' => ucwords(str_replace('_', ' ', $field)),
                                                ])

                                                @isset($change['type'])
                                                    <span class="inline-flex items-center text-xs rounded-md">
                                                        {{ ucfirst($change['type']) }}
                                                    </span>
                                                @endisset
                                            </span>
                                        </div>

                                        <div class="pl-6 mt-2 space-y-2">
                                            @if(isset($change['old_value']))
                                                <div class="flex items-center gap-2 group">
                                                    <span class="flex-shrink-0">
                                                        <x-heroicon-m-minus-circle
                                                            class="w-4 h-4"
                                                            @style([
                                                                'color: rgb(var(--danger-500))',
                                                            ])
                                                        />
                                                    </span>

                                                    <span
                                                        class="text-sm text-gray-500 transition-colors dark:text-gray-400"
                                                        @style([
                                                            'color: rgb(var(--danger-500))',
                                                        ])
                                                    >
                                                        @if($field === 'due_date')
                                                            {{ \Carbon\Carbon::parse($change['old_value'])->format('F j, Y') }}
                                                        @else
                                                            @if (is_array($change['old_value']))
                                                                {{ implode(', ', $change['old_value']) }}
                                                            @else
                                                                {!! $change['old_value'] !!}
                                                            @endif
                                                        @endif
                                                    </span>
                                                </div>
                                            @endif

                                            @if(isset($change['new_value']))
                                                <div class="flex items-center gap-2 group">
                                                    <span class="flex-shrink-0">
                                                        <x-heroicon-m-plus-circle
                                                            class="w-4 h-4 text-green-500"
                                                            @style([
                                                                'color: rgb(var(--success-500))',
                                                            ])
                                                        />
                                                    </span>

                                                    <span class="text-sm font-medium text-gray-900 transition-colors dark:text-gray-100"
                                                            @style([
                                                                'color: rgb(var(--success-500))',
                                                            ])>
                                                        @if($field === 'due_date')
                                                            {{ \Carbon\Carbon::parse($change['new_value'])->format('F j, Y') }}
                                                        @else
                                                            @if (is_array($change['new_value']))
                                                                {{ implode(', ', $change['new_value']) }}
                                                            @else
                                                                {!! $change['new_value'] !!}
                                                            @endif
                                                        @endif
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                @break
        @endSwitch
    </div>
</x-dynamic-component>
