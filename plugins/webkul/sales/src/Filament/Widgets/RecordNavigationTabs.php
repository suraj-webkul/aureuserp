<?php

namespace Webkul\Sale\Filament\Widgets;

use Filament\Widgets\Widget;

class RecordNavigationTabs extends Widget
{
    protected string $view = 'sales::filament.widgets.record-navigation-tabs';

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public $navigationItems;

    public function mount($navigationItems): void
    {
        $this->navigationItems = $navigationItems;
    }
}
