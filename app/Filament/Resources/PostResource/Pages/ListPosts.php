<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListPosts extends ListRecords
{
  protected static string $resource = PostResource::class;


  public function getTabs(): array
  {
    return [
      'Todos' => Tab::make('Todos posts')
        ->icon('heroicon-o-home'),
      'Publicados' => Tab::make('Posts publicados')
        ->icon('heroicon-o-check-circle')
        ->badge(function () {
          return static::getModel()::where('is_published', true)->count();
        })
        ->badgeColor(function () {
          return static::getModel()::where('is_published', true)->count() > 10 ? 'success' : 'warning';
        })
        ->query(function ($query) {
          $query->where('is_published', true);
        }),
      'Não Publicados' => Tab::make('Posts não publicados')
        ->icon('heroicon-o-x-circle')
        ->badge(function () {
          return static::getModel()::where('is_published', false)->count();
        })
        ->badgeColor(function () {
          return static::getModel()::where('is_published', false)->count() > 10 ? 'warning' : 'success';
        })
        ->query(function ($query) {
          $query->where('is_published', false);
        }),
    ];
  }

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make(),
    ];
  }
}
