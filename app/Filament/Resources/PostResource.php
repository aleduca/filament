<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Category;
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
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint\Operators\IsRelatedToOperator;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class PostResource extends Resource
{
  protected static ?string $model = Post::class;

  // protected static ?string $navigationIcon = 'heroicon-o-document-text';
  protected static ?int $navigationSort = 2;

  // protected static bool $shouldRegisterNavigation = false;

  protected static ?string $navigationGroup = 'Posts';

  protected static ?string $recordTitleAttribute = 'title';

  public static function getNavigationBadge(): ?string
  {
    return static::getModel()::where('is_published', false)->count() . ' unpublished';
  }

  public static function getNavigationBadgeColor(): string|array|null
  {
    return static::getModel()::where('is_published', false)->count() > 10 ? 'warning' : 'success';
  }

  public static function getGloballySearchableAttributes(): array
  {
    return ['title', 'slug', 'user.name', 'category.name'];
  }

  public static function getGlobalSearchEloquentQuery(): Builder
  {
    return parent::getGlobalSearchEloquentQuery()->with(['user', 'category']);
  }

  protected static int $globalSearchResultsLimit = 10;

  public static function getGlobalSearchResultDetails(Model $record): array
  {
    return [
      'Author' => $record->user->name,
      'Category' => $record->category->name,
    ];
  }

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
      ->filtersTriggerAction(function ($action) {
        return $action->button()->label('Filtrar Posts');
      })
      ->filtersFormWidth(MaxWidth::ExtraLarge)
      ->columns([
        TextColumn::make('title')
          ->searchable()
          ->wrap()
          ->description(function (Post $record) {
            return Str::of($record->content)->limit(60);
          })
          ->sortable(),

        TextColumn::make('tags.tag_name')
          ->badge()
          ->color(function ($state) {
            if (in_array($state, ['baby', 'garden'])) {
              return 'success';
            }
          })
          ->label('Tags'),

        ToggleColumn::make('is_published')
          ->sortable(),

        TextColumn::make('user.name')
          ->label('Author')
          ->sortable()
          ->searchable(),

        SelectColumn::make('category_id')
          ->label('Category')
          ->options(Category::pluck('name', 'id'))

        // TextColumn::make('category.name')
        //   ->label('Category')
        //   ->sortable()
        //   ->searchable()
      ])->striped()->paginated([
        10, 25, 50, 100, 'all'
      ])
      ->filters([
        SelectFilter::make('user_id')
          ->label('Author')
          ->searchable()
          ->preload()
          ->multiple()
          ->relationship('user', 'name'),

        SelectFilter::make('category_id')
          ->label('Category')
          ->searchable()
          ->preload()
          ->multiple()
          ->relationship('category', 'name'),
      ], layout: FiltersLayout::AboveContent)
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
