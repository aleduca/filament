<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStats extends BaseWidget
{
  use InteractsWithPageFilters;

  protected static ?int $sort = 2;

  protected static ?string $pollingInterval = '45s';

  protected function getColumns(): int
  {
    return 3;
  }

  protected function getStats(): array
  {
    $startDate = $this->filters['startDate'] ?? null;
    $endDate = $this->filters['endDate'] ?? null;

    return [
      Stat::make('Users Count', '0')
        ->value(function () use ($startDate, $endDate) {
          return User::whereBetween('created_at', [$startDate, $endDate])->count();
        })
        ->description('Total number of users')
        ->descriptionIcon('heroicon-o-users')
        ->descriptionColor('success')
        ->chart([7, 2, 10, 3, 15, 4, 17]),
      Stat::make('Users Admin', User::where('is_admin', true)->count())
        ->description('Total number of admin users')
        ->descriptionIcon('heroicon-o-user-circle')
        ->descriptionColor('warning'),
      Stat::make('Average time on page', '3:12'),
    ];
  }
}
