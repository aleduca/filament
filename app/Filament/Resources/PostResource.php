<?php

namespace App\Filament\Resources;

use App\Filament\Exports\PostExporter;
use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Tests\Component;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section as ComponentsSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\BooleanConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\RelationshipConstraint\Operators\IsRelatedToOperator;
use Filament\Tables\Filters\QueryBuilder\Constraints\SelectConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Filament\Tables\Filters\SelectFilter;
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

        QueryBuilder::make()
          ->constraints([
            TextConstraint::make('title')
              ->label('Title')
              ->icon('heroicon-o-document-text'),
            TextConstraint::make('author')
              ->relationship('user', 'name')
              ->label('Author')
              ->icon('heroicon-o-user'),
            BooleanConstraint::make('is_published')
              ->label('Published')
              ->icon('heroicon-o-check-circle'),
            BooleanConstraint::make('is_admin')
              ->relationship('user', 'is_admin')
              ->label('Is Admin?')
              ->icon('heroicon-o-check-circle'),
            SelectConstraint::make('email')
              ->relationship('user', 'email')
              ->icon('heroicon-o-envelope-open')
              ->options(User::pluck('email', 'email')->toArray())
              ->multiple(),
            RelationshipConstraint::make('tags')
              ->label('Tags')
              ->icon('heroicon-o-tag'),
            RelationshipConstraint::make('user')
              ->label('Author')
              ->icon('heroicon-o-user')
              ->selectable(
                IsRelatedToOperator::make()
                  ->titleAttribute('name')
                  ->preload()
                  ->searchable()
                  ->multiple(),
              ),
            RelationshipConstraint::make('category')
              ->label('category')
              ->icon('heroicon-o-tag')
              ->selectable(
                IsRelatedToOperator::make()
                  ->titleAttribute('name')
                  ->preload()
                  ->searchable()
                  ->multiple(),
              ),
          ])

      ], layout: FiltersLayout::AboveContent)
      ->headerActions([
        ExportAction::make()
          ->exporter(PostExporter::class)
          ->formats([
            ExportFormat::Xlsx
          ])
          ->icon('heroicon-o-arrow-down-on-square')
      ])
      ->actions([
        ActionGroup::make([
          Tables\Actions\ViewAction::make()->color('warning')->icon('heroicon-o-eye')->label('Ver Post'),
        ]),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
          ExportBulkAction::make()
            ->exporter(PostExporter::class)
        ]),
      ]);
  }

  public static function getRelations(): array
  {
    return [
      //
    ];
  }

  public static function infolist(Infolist $infolist): Infolist
  {
    return $infolist
      ->schema([
        ComponentsSection::make('Post Data')
          ->icon('heroicon-o-document-text')
          ->description('Post Data')
          ->schema([
            TextEntry::make('title')
              ->label('Title'),

            TextEntry::make('slug')
              ->label('Slug'),

            TextEntry::make('category.name')
              ->label('Category')
              ->getStateUsing(function ($record) {
                return strtoupper($record->category->name);
              }),

            TextEntry::make('tags.tag_name')
              ->label('Tags')
              ->badge(),

            TextEntry::make('is_published')
              ->label('Is Published')
              ->getStateUsing(function ($record) {
                return $record->is_published ? 'Yes' : 'No';
              })
          ])->columns(3),

        ComponentsSection::make('Author Information')
          ->icon('heroicon-o-user')
          ->description('Author Information')
          ->schema([
            ImageEntry::make('user.avatar')
              ->circular()
              ->label('Avatar'),

            Group::make([
              TextEntry::make('user.name')
                ->icon('heroicon-o-user')
                ->label('Author'),
              TextEntry::make('user.email')
                ->icon('heroicon-o-envelope-open')
                ->label('Email'),

              TextEntry::make('user.phone')
                ->icon('heroicon-o-phone')
                ->label('Phone'),
              TextEntry::make('user.is_admin')
                ->label('Is Admin?')
                ->color(function ($record) {
                  return $record->user->is_admin ? 'success' : 'danger';
                })
                ->icon(function ($record) {
                  return $record->is_admin ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle';
                })
                ->iconColor(function ($record) {
                  return $record->is_admin ? 'success' : 'danger';
                })
                ->getStateUsing(function ($record) {
                  return $record->user->is_admin ? 'Yes' : 'No';
                }),
            ])->columns(2),

          ])->columns(2),

        ComponentsSection::make('Thumbnail and Content')
          ->icon('heroicon-o-photo')
          ->description('Thumbnail and Content')
          ->columns(2)
          ->schema([
            ImageEntry::make('thumbnail')
              ->label('Thumbnail'),

            TextEntry::make('content')->html()
              ->label('Content')
              ->words(30)
          ])
      ]);
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListPosts::route('/'),
      'create' => Pages\CreatePost::route('/create'),
      'view' => Pages\ViewPost::route('/{record}'),
      // 'edit' => Pages\EditPost::route('/{record}/edit'),
    ];
  }
}
