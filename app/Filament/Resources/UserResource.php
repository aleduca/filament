<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
  protected static ?string $model = User::class;
  // protected static ?string $slug = '/users/index';

  protected static ?string $navigationIcon = 'heroicon-o-users';
  protected static ?string $modelLabel = 'Usuários';

  protected static ?int $navigationSort = 1;

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Tabs::make('tabs')
          ->tabs([
            Tab::make('User data')
              ->badge(5)
              ->badgeColor('success')
              ->icon('heroicon-o-user')
              ->schema([
                Section::make('Informações do usuário')
                  ->schema([
                    TextInput::make('name')
                      ->helperText('Nome do usuário')
                      ->hint('Nome do usuário')
                      ->rules(['required'])
                      ->label('Nome')
                      ->placeholder('Nome do usuário')
                      ->required(),

                    TextInput::make('email')
                      ->helperText('Email do usuário')
                      ->hint('Email do usuário')
                      ->rules(['required'])
                      ->unique(ignoreRecord: true)
                      ->email()
                      ->placeholder('Email')
                      ->required(),

                    TextInput::make('password')
                      ->helperText('Password do usuário')
                      ->hint('Password do usuário')
                      ->rules(['required'])
                      ->password()
                      ->placeholder('Password')
                      ->required()
                      ->visibleOn('create'),

                    TextInput::make('phone')
                      ->helperText('Telefone do usuário')
                      ->hint('Telefone do usuário')
                      ->mask('(99) 99999-9999')
                      ->placeholder('(__) _____-____'),
                  ])

              ]),
            Tab::make('Avatar')
              ->icon('heroicon-o-user')
              ->schema([
                Section::make('Avatar')
                  ->schema([
                    FileUpload::make('avatar')
                      ->image()
                      ->directory('avatars')
                      ->imageEditor(),
                  ])
              ]),

            Tab::make('is admin')
              ->icon('heroicon-o-user')
              ->schema([
                Section::make('Is Admin')
                  ->schema([
                    Toggle::make('is_admin')
                      ->helperText('Usuário é admin?')
                      ->hint('Escolha status do usuário'),
                  ])
              ])
          ])->activeTab(1)->contained(false)->columnSpanFull(),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('name')
          ->label('Nome')
          ->sortable()
          ->searchable(),

        ImageColumn::make('avatar')
          ->circular(),

        TextColumn::make('email')
          ->sortable()
          ->label('Email'),

        IconColumn::make('is_admin')
          ->label('Admin?')
          ->boolean(),

        TextColumn::make('phone')
          ->label('Telefone')
          ->toggleable(isToggledHiddenByDefault: true),

        TextColumn::make('comments_count')
          ->label('Comentários')
          ->sortable()
          ->counts('comments'),

        TextColumn::make('created_at')
          ->dateTime()
          ->toggleable(isToggledHiddenByDefault: true),

        TextColumn::make('updated_at')
          ->dateTime()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        //
      ])
      ->actions([
        ActionGroup::make([
          Tables\Actions\EditAction::make()->color('primary')->icon('heroicon-o-pencil')->label('Editar usuário'),
          Tables\Actions\DeleteAction::make()->color('danger')->icon('heroicon-o-trash')->label('Deletar usuário'),
        ]),
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
      'index' => Pages\ListUsers::route('/'),
      'create' => Pages\CreateUser::route('/create'),
      'edit' => Pages\EditUser::route('/{record}/edit'),
    ];
  }

  public static function getNavigationBadge(): ?string
  {
    return static::$model::count();
  }
}
