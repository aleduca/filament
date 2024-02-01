<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Actions;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPost extends ViewRecord
{
  protected static string $resource = PostResource::class;


  protected function getHeaderActions(): array
  {
    return [
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
