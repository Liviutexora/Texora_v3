<?php

namespace App\Filament\Widgets;

use App\Filament\Concerns\HasWidgetPermissions;
use App\Models\Role;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Str;

class UsersDoughnutChart extends ChartWidget
{
    use HasWidgetPermissions;

    protected int|string|array $columnSpan = 2;

    public function getHeading(): ?string
    {
        return __('User Roles Distribution');
    }

    protected function getData(): array
    {
        // Fetch roles with user counts
        $roles = Role::withCount('users')->get();

        // Labels = role names (translated)
        $labels = $roles->pluck('name')->map(function ($roleName) {
            $label = Str::of($roleName)->replace('_', ' ')->title()->toString();
            return __($label);
        })->toArray();

        // Data = number of users per role
        $data = $roles->pluck('users_count')->toArray();

        // Assign colors (cycle if roles > 5)
        $colors = [
            '#f87171', // Red
            '#fbbf24', // Yellow
            '#fb923c', // Orange
            '#34d399', // Green
            '#3b82f6', // Blue
            '#8b5cf6', // Purple
            '#ec4899', // Pink
            '#10b981', // Emerald
        ];
        $backgroundColors = [];
        foreach ($roles as $index => $role) {
            $backgroundColors[] = $colors[$index % count($colors)];
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderWidth' => 0, // no borders
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    public function getColumns(): int | array
    {
        return [
            'sm' => 2,
            'md' => 2,
            'xl' => 2,
        ];
    }
}
