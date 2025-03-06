<?php

namespace Webkul\Sale\Filament\Clusters\Orders\Resources\QuotationResource\Actions;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Webkul\Partner\Models\Partner;
use Webkul\Sale\Enums\InvoiceStatus;
use Webkul\Sale\Enums\OrderState;
use Webkul\Sale\Mail\SaleOrderCancelQuotation;
use Webkul\Support\Services\EmailService;

class CancelQuotationAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'orders.sales.confirm';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->color('gray')
            ->label(__('sales::traits/sale-order-action.header-actions.cancel.title'))
            ->modalIcon('heroicon-s-x-circle')
            ->modalHeading(__('sales::traits/sale-order-action.header-actions.cancel.modal.heading'))
            ->modalDescription(__('sales::traits/sale-order-action.header-actions.cancel.modal.description'))
            ->modalFooterActions(function ($record, $livewire): array {
                return [
                    Action::make('sendAndCancel')
                        ->label(__('sales::traits/sale-order-action.header-actions.cancel.footer-actions.send-and-cancel.title'))
                        ->icon('heroicon-o-envelope')
                        ->modalIcon('heroicon-s-envelope')
                        ->action(function () use ($record, $livewire) {
                            $record->update([
                                'state'          => OrderState::CANCEL->value,
                                'invoice_status' => InvoiceStatus::NO->value,
                            ]);

                            if ($livewire?->mountedActionsData[0]) {
                                $this->handleCancelAndSendEmail($record, $livewire?->mountedActionsData[0]);
                            }

                            $this->refreshFormData(['state']);

                            Notification::make()
                                ->success()
                                ->title(__('sales::traits/sale-order-action.header-actions.cancel.footer-actions.send-and-cancel.notification.cancelled.title'))
                                ->body(__('sales::traits/sale-order-action.header-actions.cancel.footer-actions.send-and-cancel.notification.cancelled.body'))
                                ->send();
                        })
                        ->cancelParentActions(),
                    Action::make('cancel')
                        ->label(__('sales::traits/sale-order-action.header-actions.cancel.footer-actions.cancel.title'))
                        ->icon('heroicon-o-x-circle')
                        ->modalIcon('heroicon-s-x-circle')
                        ->action(function () use ($record) {
                            $record->update([
                                'state'          => OrderState::CANCEL->value,
                                'invoice_status' => InvoiceStatus::NO->value,
                            ]);

                            $this->refreshFormData(['state']);

                            Notification::make()
                                ->success()
                                ->title(__('sales::traits/sale-order-action.header-actions.cancel.footer-actions.cancel.notification.cancelled.title'))
                                ->body(__('sales::traits/sale-order-action.header-actions.cancel.footer-actions.cancel.notification.cancelled.body'))
                                ->send();
                        })
                        ->cancelParentActions(),
                    Action::make('close')
                        ->color('gray')
                        ->label(__('sales::traits/sale-order-action.header-actions.cancel.footer-actions.close.title'))
                        ->cancelParentActions(),
                ];
            })
            ->form(
                function (Form $form, $record) {
                    return $form->schema([
                        Forms\Components\Select::make('partners')
                            ->options(Partner::all()->pluck('name', 'id'))
                            ->multiple()
                            ->default([$record->partner_id])
                            ->searchable()
                            ->label(__('sales::traits/sale-order-action.header-actions.cancel.form.fields.partner'))
                            ->preload(),
                        Forms\Components\TextInput::make('subject')
                            ->default(fn () => __('sales::traits/sale-order-action.header-actions.cancel.form.fields.subject-default', [
                                'name' => $record->name,
                                'id'   => $record->id,
                            ]))
                            ->placeholder(__('sales::traits/sale-order-action.header-actions.cancel.form.fields.subject-placeholder'))
                            ->label(__('sales::traits/sale-order-action.header-actions.cancel.form.fields.subject'))
                            ->hiddenLabel(),
                        Forms\Components\RichEditor::make('description')
                            ->label(__('sales::traits/sale-order-action.header-actions.cancel.form.fields.description'))
                            ->default(function () use ($record) {
                                return __('sales::traits/sale-order-action.header-actions.cancel.form.fields.description-default', [
                                    'partner_name' => $record?->partner?->name,
                                    'name'         => $record?->name,
                                ]);
                            })
                            ->hiddenLabel(),
                    ]);
                }
            )
            ->hidden(fn ($record) => ! in_array($record->state, [OrderState::DRAFT->value, OrderState::SENT->value, OrderState::SALE->value]));
    }

    private function preparePayloadForCancelAndSendEmail($record, $partner, $data): array
    {
        return [
            'record_url'     => $this->getRedirectUrl() ?? '',
            'record_name'    => $record->name,
            'model_name'     => 'Quotation',
            'subject'        => $data['subject'],
            'description'    => $data['description'],
            'to'             => [
                'address' => $partner?->email,
                'name'    => $partner?->name,
            ],
        ];
    }

    private function handleCancelAndSendEmail($record, $data)
    {
        $partners = Partner::whereIn('id', $data['partners'])->get();

        foreach ($partners as $key => $partner) {
            app(EmailService::class)->send(
                mailClass: SaleOrderCancelQuotation::class,
                view: $viewName = 'sales::mails.sale-order-cancel-quotation',
                payload: $this->preparePayloadForCancelAndSendEmail($record, $partner, $data),
            );
        }

        $messageData = [
            'from' => [
                'company' => Auth::user()->defaultCompany->toArray(),
            ],
            'body' => view($viewName, [
                'payload' => $this->preparePayloadForCancelAndSendEmail($record, $partner, $data),
            ])->render(),
            'type' => 'comment',
        ];

        $record->addMessage($messageData, Auth::user()->id);
    }
}
