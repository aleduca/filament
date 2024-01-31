<?php

namespace App\Filament\Exports;

use App\Models\Post;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Components\TextInput;

class PostExporter extends Exporter
{
  protected static ?string $model = Post::class;

  public static function getColumns(): array
  {
    return [
      ExportColumn::make('title'),
      ExportColumn::make('user.name')
        ->label('Author'),
      ExportColumn::make('category.name')
        ->label('Category'),
      ExportColumn::make('tags.tag_name')
        ->label('Tags')
        ->formatStateUsing(function ($state) {
          return empty($state) ? 'No tags' : $state;
        }),
      ExportColumn::make('is_published')
        ->label('Is Published?')
        ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),
      ExportColumn::make('content')
        ->formatStateUsing(function ($state, $options) {
          return str($state)->words($options['wordsLimit'] ?? 40);
        })
    ];
  }

  public static function getOptionsFormComponents(): array
  {
    return [
      TextInput::make('wordsLimit')
        ->label('Words limit')
        ->integer()
    ];
  }

  public static function getCompletedNotificationBody(Export $export): string
  {
    $body = 'Your post export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

    if ($failedRowsCount = $export->getFailedRowsCount()) {
      $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
    }

    return $body;
  }
}
