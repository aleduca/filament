<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Str;

class ViewCategory extends ViewRecord
{
  protected static string $resource = CategoryResource::class;

  protected function getHeaderActions(): array
  {
    return [
      CreateAction::make()
        ->model(Category::class)
        ->mutateFormDataUsing(function (array $data): array {
          $data['slug'] = Str::slug($data['name']);

          return $data;
        })
        ->form([
          TextInput::make('name')
            ->required()
            ->maxLength(255),
        ]),
      EditAction::make()
        ->color('primary')
        ->slideOver()
        ->size('sm')
        ->icon('heroicon-o-pencil')
        ->label('Editar Post'),
      DeleteAction::make()
        ->color('danger')
        ->size('sm')
        ->requiresConfirmation()
        ->icon('heroicon-o-trash')
        ->label('Deletar Post'),
    ];
  }
}
