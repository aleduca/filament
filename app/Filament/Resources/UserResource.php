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
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
  protected static ?string $model = User::class;
  // protected static ?string $slug = '/users/index';

  protected static ?string $navigationIcon = 'heroicon-o-users';
  protected static ?string $navigationLabel = 'Usuários';

  protected static ?int $navigationSort = 1;

  protected static ?string $recordTitleAttribute = 'name';


  // public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
  // {
  //   return 'Nome do user: ' . $record->name;
  // }

  protected static int $globalSearchResultsLimit = 10;

  public static function getGloballySearchableAttributes(): array
  {
    return ['name', 'email'];
  }

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
      ->filtersTriggerAction(function ($action) {
        return $action->button()->label('Filtrar');
      })
      ->filtersFormWidth(MaxWidth::ExtraLarge)
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

        ToggleColumn::make('is_admin')
          ->label('Admin?'),

        TextColumn::make('phone')
          ->label('Telefone')
          ->toggleable(isToggledHiddenByDefault: true),

        TextColumn::make('comments_count')
          ->label('Comentários')
          ->sortable()
          ->badge()
          ->icon('heroicon-o-chat-bubble-bottom-center-text')
          ->color(function ($state): string {
            return ($state >= 2) ? 'success' : 'danger';
          })
          ->counts('comments'),

        TextColumn::make('created_at')
          ->dateTime()
          ->toggleable(isToggledHiddenByDefault: true),

        TextColumn::make('updated_at')
          ->dateTime()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        TernaryFilter::make('is_admin'),

        SelectFilter::make('id')
          ->label('Users')
          ->multiple()
          ->options(User::pluck('name', 'id')),

        QueryBuilder::make()
          ->constraints([
            TextConstraint::make('name')
              ->label('Nome')
              ->icon('heroicon-o-user'),

            TextConstraint::make('email_verified_at')
              ->label('Email Verified?')
              ->icon('heroicon-o-envelope-open')
              ->nullable(),
          ])

      ], layout: FiltersLayout::AboveContent)
      ->actions([
        ActionGroup::make([
          Tables\Actions\EditAction::make()->color('primary')->icon('heroicon-o-pencil')->label('Editar usuário'),
          Tables\Actions\DeleteAction::make()->color('danger')->icon('heroicon-o-trash')->label('Deletar usuário'),
          Tables\Actions\ViewAction::make()
            ->slideOver()
            ->color('primary')
            ->icon('heroicon-o-document-text')
            ->label('Ver usuário'),
          Action::make('is_admin')
            ->label(function (User $record) {
              return $record->is_admin ? 'Remover admin' : 'Tornar admin';
            })
            ->color(function (User $record) {
              return $record->is_admin ? 'danger' : 'success';
            })
            ->icon('heroicon-o-user')
            ->action(function (User $record) {
              $record->is_admin = !$record->is_admin;
              $record->save();
            })
            ->after(function (User $record) {
              if ($record->is_admin) {
                Notification::make()
                  ->success()
                  ->duration(1000)
                  ->title('Usuário é admin')
                  ->body('Usuário agora é admin')
                  ->send();
              } else {
                Notification::make()
                  ->danger()
                  ->duration(1000)
                  ->title('Usuário não é admin')
                  ->body('Usuário agora não é mais admin')
                  ->send();
              }
            })
        ]),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
          BulkAction::make('is_admin')
            ->icon('heroicon-o-user')
            ->color('primary')
            ->action(function ($records) {
              $records->each(function ($record) {
                $record->is_admin = !$record->is_admin;
                $record->save();
              });
            })
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
      // 'view' => Pages\ViewUser::route('/{record}'),
    ];
  }

  public static function getNavigationBadge(): ?string
  {
    return static::$model::count();
  }
}
