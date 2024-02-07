<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;

class ListCategories extends ListRecords
{
  protected static string $resource = CategoryResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->slideOver()
        ->label('Add Category')
        ->icon('heroicon-o-plus')
        ->mutateFormDataUsing(function (array $data): array {
          $data['slug'] = Str::slug($data['name']);

          return $data;
        })
        ->form([
          TextInput::make('name')
            ->required()
            ->maxLength(255),
        ])
        ->successNotification(
          Notification::make()
            ->success()
            ->title('category created')
            ->body('The category has been created successfully.'),
        )
    ];
  }
}
