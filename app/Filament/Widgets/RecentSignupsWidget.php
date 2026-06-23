<?php

namespace App\Filament\Widgets;

use App\Models\Tenant;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentSignupsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Recent signups (last 30 days)'))
            ->query(
                Tenant::query()
                    ->where('created_at', '>=', now()->subDays(30))
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Business'))
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('owner.name')
                    ->label(__('Owner'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('owner.email')
                    ->label(__('Email'))
                    ->copyable(),

                Tables\Columns\BadgeColumn::make('stripe_subscription_status')
                    ->label(__('Subscription'))
                    ->colors([
                        'success' => 'active',
                        'warning' => 'trialing',
                        'danger'  => ['past_due', 'canceled', 'unpaid'],
                        'gray'    => fn ($state) => $state === null,
                    ])
                    ->formatStateUsing(fn ($state) => $state ? ucfirst(str_replace('_', ' ', $state)) : __('No subscription')),

                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('Status'))
                    ->colors([
                        'success' => 'active',
                        'danger'  => 'suspended',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Signed up'))
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([5, 10, 25])
            ->emptyStateHeading(__('No signups in the last 30 days'))
            ->emptyStateIcon('heroicon-o-building-storefront');
    }
}
