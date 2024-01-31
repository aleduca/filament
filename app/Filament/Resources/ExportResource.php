<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExportResource\Pages;
use App\Filament\Resources\ExportResource\RelationManagers;
use App\Models\Export;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExportResource extends Resource
{
  protected static ?string $model = Export::class;

  protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

  protected static ?string $navigationLabel = 'Download Reports';

  protected static ?int $navigationSort = 4;

  protected static ?string $recordTitleAttribute = 'name';

  public static function canCreate(): bool
  {
    return false;
  }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        //
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('file_name')
          ->label('File Name')
          ->url(function (Export $export) {
            return route(
              'filament.exports.download',
              [
                'export' => $export,
                'format' => 'xlsx',
              ],
              absolute: false
            );
          })
          ->icon('heroicon-o-arrow-down-tray'),

        TextColumn::make('completed_at')
          ->label('Completed At')
          ->icon('heroicon-o-clock')
          ->formatStateUsing(function ($state) {
            return $state ? Carbon::parse($state)->format('d/m/Y H:i:s') : null;
          }),
      ])
      ->filters([
        //
      ])
      ->actions([
        Action::make('download')
          ->label('Download')
          ->icon('heroicon-o-arrow-down-tray')
          ->url(function (Export $export) {
            return route(
              'filament.exports.download',
              [
                'export' => $export,
                'format' => 'xlsx',
              ],
              absolute: false
            );
          })
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
        ]),
      ]);
  }

  public static function getRelations(): array
  {
    return [
      //
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListExports::route('/'),
      // 'create' => Pages\CreateExport::route('/create'),
      // 'edit' => Pages\EditExport::route('/{record}/edit'),
    ];
  }
}
