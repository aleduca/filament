<?php

namespace App\Filament\Exports;

use App\Models\User;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class UserExporter extends Exporter
{
  protected static ?string $model = User::class;

  public static function getColumns(): array
  {
    return [
      ExportColumn::make('name'),
      ExportColumn::make('email')
        ->label('E-mail'),
      ExportColumn::make('created_at')
        ->label('Created At')
        ->formatStateUsing(function ($state) {
          return $state->format('d/m/Y H:i:s');
        }),
    ];
  }

  public static function getCompletedNotificationBody(Export $export): string
  {
    $body = 'Your user export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

    if ($failedRowsCount = $export->getFailedRowsCount()) {
      $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
    }

    return $body;
  }
}
