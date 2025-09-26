@props([
    'active' => false,
    'collapsible' => true,
    'icon' => null,
    'items' => [],
    'label' => null,
    'sidebarCollapsible' => true,
    'subNavigation' => false,
])

<ul
    @if ($sidebarCollapsible)
        x-transition:enter="fi-transition-enter"
        x-transition:enter-start="fi-transition-enter-start"
        x-transition:enter-end="fi-transition-enter-end"
    @endif
    class="fi-sidebar-group-items"
>
    @foreach ($items as $item)
        @php
            $isItemActive = $item->isActive();
            $isItemChildItemsActive = $item->isChildItemsActive();
            $itemActiveIcon = $item->getActiveIcon();
            $itemBadge = $item->getBadge();
            $itemBadgeColor = $item->getBadgeColor();
            $itemBadgeTooltip = $item->getBadgeTooltip();
            $itemChildItems = $item->getChildItems();
            $itemIcon = $item->getIcon();
            $shouldItemOpenUrlInNewTab = $item->shouldOpenUrlInNewTab();
            $itemUrl = $item->getUrl();
        @endphp

        <x-filament-panels::sidebar.item
            :active="$isItemActive"
            :active-child-items="$isItemChildItemsActive"
            :active-icon="$itemActiveIcon"
            :badge="$itemBadge"
            :badge-color="$itemBadgeColor"
            :badge-tooltip="$itemBadgeTooltip"
            :child-items="$itemChildItems"
            :first="$loop->first"
            :grouped="false"
            :icon="$itemIcon"
            :last="$loop->last"
            :should-open-url-in-new-tab="$shouldItemOpenUrlInNewTab"
            :sidebar-collapsible="$sidebarCollapsible"
            :url="$itemUrl"
        >
            {{ $item->getLabel() }}

            @if ($itemIcon instanceof \Illuminate\Contracts\Support\Htmlable)
                <x-slot name="icon">
                    {{ $itemIcon }}
                </x-slot>
            @endif

            @if ($itemActiveIcon instanceof \Illuminate\Contracts\Support\Htmlable)
                <x-slot name="activeIcon">
                    {{ $itemActiveIcon }}
                </x-slot>
            @endif
        </x-filament-panels::sidebar.item>
    @endforeach
</ul>
