<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Mail\DemoMail;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateCategory extends CreateRecord
{
  protected static string $resource = CategoryResource::class;

  protected function mutateFormDataBeforeCreate(array $data): array
  {
    $data['slug'] = Str::slug($data['name']);

    return $data;
  }

  protected function getCreatedNotification(): ?Notification
  {
    return Notification::make()
      ->success()
      ->title('Category registered')
      ->body('The category has been created successfully.');
  }

  protected function afterCreate(): void
  {
    // Mail::to($this->record->email)->send(new DemoMail(['subject' => 'Category Created', 'message' => 'Category Created'], $this->record));
  }
}
