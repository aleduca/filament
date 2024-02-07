<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class PostChart extends ChartWidget
{
  protected static ?string $heading = 'Chart';

  protected static ?int $sort = 3;

  protected static ?string $pollingInterval = '5s';

  protected function getData(): array
  {
    $data = Trend::model(Post::class)
      ->between(
        start: now()->startOfYear(),
        end: now()->endOfYear(),
      )
      ->perMonth()
      ->count();

    return [
      'datasets' => [
        [
          'label' => 'Blog posts',
          'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
        ],
      ],
      'labels' => $data->map(fn (TrendValue $value) => $value->date),
    ];
  }

  // protected function getData(): array
  // {
  //   $data = $this->getPostsCreatedByMonth();
  //   return [
  //     'datasets' => [
  //       [
  //         'label' => 'Blog posts created',
  //         'data' => $data['data'],
  //       ],
  //     ],
  //     'labels' => $data['labels'],
  //   ];
  // }

  private function getPostsCreatedByMonth()
  {
    $perMonth = [];

    $months = collect(range(1, 12))->map(function ($month) use (&$perMonth) {
      $count = Post::whereMonth('created_at', $month)
        ->whereYear('created_at', now()->year)
        ->count();

      $perMonth[] = $count;

      return now()->month($month)->format('M');
    });

    return [
      'labels' => $months,
      'data' => $perMonth
    ];
  }

  protected function getType(): string
  {
    return 'line';
  }
}
