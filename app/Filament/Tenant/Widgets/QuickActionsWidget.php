<?php

namespace App\Filament\Tenant\Widgets;

use App\Support\TenantContext;
use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected static ?int $sort = 0;

    protected string $view = 'filament.tenant.widgets.quick-actions';

    protected int | string | array $columnSpan = 'full';

    public function getViewData(): array
    {
        $tenant  = TenantContext::current();
        $slug    = $tenant?->slug;
        $bookingPageUrl = $slug ? url("/{$slug}") : null;

        return [
            'bookingPageUrl' => $bookingPageUrl,
            'actions'        => [
                [
                    'label'    => __('New Booking'),
                    'url'      => route('filament.tenant.resources.bookings.index'),
                    'icon'     => 'heroicon-o-calendar-days',
                    'color'    => 'violet',
                    'external' => false,
                ],
                [
                    'label'    => __('Add Provider'),
                    'url'      => route('filament.tenant.resources.providers.create'),
                    'icon'     => 'heroicon-o-user-plus',
                    'color'    => 'blue',
                    'external' => false,
                ],
                [
                    'label'    => __('Add Service'),
                    'url'      => route('filament.tenant.resources.services.create'),
                    'icon'     => 'heroicon-o-plus-circle',
                    'color'    => 'green',
                    'external' => false,
                ],
                [
                    'label'    => __('View Booking Page'),
                    'url'      => $bookingPageUrl,
                    'icon'     => 'heroicon-o-arrow-top-right-on-square',
                    'color'    => 'gray',
                    'external' => true,
                ],
            ],
        ];
    }
}
