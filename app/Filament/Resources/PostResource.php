<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class PostResource extends Resource
{
  protected static ?string $model = Post::class;

  protected static ?string $navigationIcon = 'heroicon-o-document-text';
  protected static ?int $navigationSort = 2;

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Section::make('Post Information')
          ->description('Create Title and slug')
          ->schema([
            TextInput::make('title')
              ->helperText('Post title')
              ->hint('Post title')
              ->label('Title')
              ->required()
              ->live(onBlur: true)
              ->afterStateUpdated(function ($state, $set) {
                $set('slug', Str::slug($state));
              })
              ->placeholder('Post title'),

            TextInput::make('slug')
              ->helperText('Post slug')
              ->hint('Post slug')
              ->label('Slug')
              ->required()
              ->placeholder('Post slug')
          ])->columns(2),

        Section::make('Post Content')
          ->description('Post Content')
          ->schema([
            RichEditor::make('content')
              ->helperText('Post content')
              ->hint('Post content')
              ->label('Content')
              ->required()
          ])->columnSpanFull(),

        Section::make('Post Thumb')
          ->description('Post Thumb')
          ->schema([
            FileUpload::make('thumbnail')
              ->image()
              ->helperText('Post thumb')
              ->hint('Post thumb')
              ->directory('thumbs')
              ->label('Thumb')
              ->required()
          ])->columnSpanFull(),

        Section::make('Categories and Tags')
          ->description('Select Categories and Tags')
          ->schema([
            Select::make('category_id')
              ->label('Category')
              ->searchable()
              ->preload()
              ->relationship('category', 'name'),

            Select::make('tags')
              ->label('Tags')
              ->searchable()
              ->multiple()
              ->preload()
              ->relationship('tags', 'tag_name')
          ])->columns(2),

        Section::make('Publish')
          ->schema([
            Select::make('is_published')
              ->label('Publish')
              ->options([
                0 => 'No',
                1 => 'Yes'
              ])
              ->required()
          ])
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('title')
          ->searchable()
          ->sortable()
          ->limit(20),

        IconColumn::make('is_published')
          ->sortable()
          ->boolean(),

        TextColumn::make('user.name')
          ->label('Author')
          ->sortable()
          ->searchable(),

        TextColumn::make('category.name')
          ->label('Category')
          ->sortable()
          ->searchable()
      ])
      ->filters([
        //
      ])
      ->actions([
        ActionGroup::make([
          Tables\Actions\EditAction::make()->color('primary')->icon('heroicon-o-pencil')->label('Editar Post'),
          Tables\Actions\DeleteAction::make()->color('danger')->icon('heroicon-o-trash')->label('Deletar Post'),
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
      'index' => Pages\ListPosts::route('/'),
      'create' => Pages\CreatePost::route('/create'),
      'edit' => Pages\EditPost::route('/{record}/edit'),
    ];
  }
}
