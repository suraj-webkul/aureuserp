<?php

namespace Webkul\Invoice\Filament\Clusters\Vendors\Resources\RefundResource\Pages;

use Illuminate\Database\Eloquent\Builder;
use Webkul\Account\Enums\MoveType;
use Webkul\Account\Filament\Resources\InvoiceResource\Pages\ListInvoices as BaseListInvoices;
use Webkul\Invoice\Filament\Clusters\Vendors\Resources\RefundResource;
use Webkul\TableViews\Filament\Components\PresetView;
use Webkul\TableViews\Filament\Concerns\HasTableViews;

class ListRefunds extends BaseListInvoices
{
    use HasTableViews;

    protected static string $resource = RefundResource::class;

    public function getPresetTableViews(): array
    {
        $presets = parent::getPresetTableViews();

        return array_merge(
            $presets,
            [
                'in_refund' => PresetView::make(__('invoices::filament/clusters/vendors/resources/refund/pages/list-refund.tabs.refund'))
                    ->icon('heroicon-s-receipt-refund')
                    ->default()
                    ->favorite()
                    ->modifyQueryUsing(fn (Builder $query) => $query->where('move_type', MoveType::IN_REFUND->value)),
            ]
        );
    }
}
