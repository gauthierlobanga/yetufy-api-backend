<?php

namespace App\Filament\Vendeur\Resources\Posts\Schemas;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\MentionProvider;
use Filament\Forms\Components\RichEditor\TextColor;
use Filament\Forms\Components\RichEditor\ToolbarButtonGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                // Section principale - occupe 2 colonnes sur 3
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Tabs::make('Post')
                            ->tabs([
                                Tab::make('Contenu')
                                    ->icon('heroicon-m-document-text')
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Titre')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                if (! $get('slug') && $state) {
                                                    $set('slug', Str::slug($state));
                                                }
                                            }),

                                        TextInput::make('slug')
                                            ->label('Slug')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true)
                                            ->helperText('Identifiant unique pour l\'URL'),

                                        RichEditor::make('content')
                                            ->label('Contenu')
                                            ->required()
                                            ->fileAttachmentsDisk('media')
                                            ->fileAttachmentsDirectory('posts/attachments')
                                            ->fileAttachmentsVisibility('public')
                                            ->columnSpanFull()
                                            ->toolbarButtons([
                                                ['bold', 'italic', 'underline', 'strike', 'link', 'italic', 'attachFiles'],
                                                [ToolbarButtonGroup::make('Heading', ['h1', 'h2', 'h3'])->textualButtons()->icon('fi-o-heading')],
                                                [ToolbarButtonGroup::make('Alignment', ['alignStart', 'alignCenter', 'alignEnd', 'alignJustify'])],
                                                ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                                                ['undo', 'redo'],
                                            ])
                                            ->floatingToolbars([
                                                'paragraph' => [
                                                    'bold',
                                                    'italic',
                                                    'underline',
                                                    'strike',
                                                    'subscript',
                                                    'superscript',
                                                ],
                                                'heading' => [
                                                    'h1',
                                                    'h2',
                                                    'h3',
                                                ],
                                                'table' => [
                                                    'tableAddColumnBefore',
                                                    'tableAddColumnAfter',
                                                    'tableDeleteColumn',
                                                    'tableAddRowBefore',
                                                    'tableAddRowAfter',
                                                    'tableDeleteRow',
                                                    'tableMergeCells',
                                                    'tableSplitCell',
                                                    'tableToggleHeaderRow',
                                                    'tableToggleHeaderCell',
                                                    'tableDelete',
                                                ],
                                            ])
                                            ->textColors([
                                                'brand' => TextColor::make('Brand', '#0ea5e9'),
                                                'warning' => TextColor::make('Warning', '#f59e0b', darkColor: '#fbbf24'),
                                                ...TextColor::getDefaults(),
                                            ])
                                            ->mentions([
                                                MentionProvider::make('@')
                                                    ->getSearchResultsUsing(fn (string $search): array => User::query()
                                                        ->where('name', 'like', "%{$search}%")
                                                        ->orderBy('name')
                                                        ->limit(10)
                                                        ->pluck('name', 'id')
                                                        ->all())
                                                    ->getLabelsUsing(fn (array $ids): array => User::query()
                                                        ->whereIn('id', $ids)
                                                        ->pluck('name', 'id')
                                                        ->all()),
                                            ]),

                                        RichEditor::make('excerpt')
                                            ->label('Extrait')
                                            ->maxLength(500)
                                            ->columnSpanFull()
                                            ->toolbarButtons([
                                                'attachFiles',
                                                'blockquote',
                                                'bold',
                                                'bulletList',
                                                'codeBlock',
                                                'h2',
                                                'h3',
                                                'italic',
                                                'link',
                                                'orderedList',
                                                'redo',
                                                'strike',
                                                'underline',
                                                'undo',
                                            ])
                                            ->helperText('Court résumé du post (optionnel)'),
                                    ]),

                                Tab::make('Média')
                                    ->icon('heroicon-m-photo')
                                    ->schema([
                                        // IMAGE À LA UNE OPTIMISÉE
                                        SpatieMediaLibraryFileUpload::make('featured')
                                            ->label('Image à la une')
                                            ->collection('featured')
                                            ->image()
                                            ->imageEditor()
                                            ->responsiveImages()
                                            ->conversion('card') // Aperçu avec la conversion card
                                            ->disk('public')
                                            ->directory('posts/featured')
                                            ->visibility('public')
                                            ->maxSize(10208) // 10MB
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->loadingIndicatorPosition('left')
                                            ->panelLayout('integrated')
                                            ->columnSpanFull()
                                            ->helperText('Format 16:9 recommandé, poids max 5MB. Formats acceptés : JPEG, PNG, WebP')
                                            ->hint('Haute résolution recommandée (min. 1600x900)'),

                                        // GALERIE D'IMAGES
                                        SpatieMediaLibraryFileUpload::make('gallery')
                                            ->label('Galerie d\'images')
                                            ->collection('gallery')
                                            ->multiple()
                                            ->image()
                                            ->imageEditor()
                                            // ->imageResizeMode('cover')
                                            ->responsiveImages()
                                            ->conversion('thumb') // Miniature dans la galerie
                                            ->disk('public')
                                            ->directory('posts/gallery')
                                            ->visibility('public')
                                            ->maxFiles(10)
                                            ->maxSize(5120)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                            ->reorderable()
                                            ->appendFiles()
                                            ->panelLayout('grid')
                                            ->columnSpanFull()
                                            ->helperText('Images supplémentaires (max 10 fichiers, 5MB chacun). Glissez pour réorganiser.'),

                                        // FICHIERS JOINTS
                                        SpatieMediaLibraryFileUpload::make('attachments')
                                            ->label('Pièces jointes')
                                            ->collection('attachments')
                                            ->multiple()
                                            ->disk('media')
                                            ->directory('posts/attachments')
                                            ->visibility('public')
                                            ->maxFiles(5)
                                            ->maxSize(10240) // 10MB
                                            ->acceptedFileTypes([
                                                'application/pdf',
                                                'application/msword',
                                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                                'text/plain',
                                            ])
                                            ->downloadable()
                                            ->previewable(true)
                                            ->openable()
                                            ->reorderable()
                                            ->appendFiles()
                                            ->columnSpanFull()
                                            ->helperText('Documents joints (PDF, DOC, DOCX, TXT - max 10MB)'),
                                    ]),

                                Tab::make('SEO')
                                    ->icon('heroicon-m-magnifying-glass')
                                    ->schema([
                                        TextInput::make('meta_title')
                                            ->label('Titre SEO')
                                            ->maxLength(60)
                                            ->helperText('Titre pour les moteurs de recherche (60 caractères max)')
                                            ->extraAttributes(['data-seo-counter' => true]),

                                        Textarea::make('meta_description')
                                            ->label('Description SEO')
                                            ->maxLength(160)
                                            ->rows(3)
                                            ->helperText('Description pour les moteurs de recherche (160 caractères max)')
                                            ->extraAttributes(['data-seo-counter' => true]),

                                        SpatieTagsInput::make('tags')
                                            ->type('categories')
                                            ->label('Mots-clés SEO')
                                            ->placeholder('Nouveau mot-clé')
                                            ->splitKeys(['Tab', ' ', ','])
                                            ->helperText('Mots-clés séparés par des virgules'),
                                    ]),
                            ]),
                    ]),

                // Sidebar droite - occupe 1 colonne
                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Publication')
                            ->icon('heroicon-m-calendar')
                            ->schema([
                                Select::make('user_id')
                                    ->label('Auteur')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->default(Auth::id()),

                                ToggleButtons::make('status')
                                    ->label('Statut')
                                    ->options(Post::getStatuses())
                                    ->colors([
                                        'draft' => 'gray',
                                        'published' => 'success',
                                        'scheduled' => 'warning',
                                        'expired' => 'danger',
                                        'archived' => 'gray',
                                    ])
                                    ->icons([
                                        'draft' => 'heroicon-m-pencil',
                                        'published' => 'heroicon-m-check-circle',
                                        'scheduled' => 'heroicon-m-clock',
                                        'expired' => 'heroicon-m-x-circle',
                                        'archived' => 'heroicon-m-archive-box',
                                    ])
                                    ->inline()
                                    ->required()
                                    ->default('draft'),

                                DateTimePicker::make('published_at')
                                    ->label('Date de publication')
                                    ->native(false)
                                    ->seconds(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->helperText('Laissez vide pour publication immédiate'),

                                DateTimePicker::make('scheduled_for')
                                    ->label('Programmé pour')
                                    ->native(false)
                                    ->seconds(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->visible(fn (Get $get): bool => $get('status') === 'scheduled')
                                    ->requiredWith('status:scheduled'),

                                DateTimePicker::make('expires_at')
                                    ->label('Expire le')
                                    ->native(false)
                                    ->seconds(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->after('published_at')
                                    ->helperText('Date d\'expiration du post'),

                                Toggle::make('is_pinned')
                                    ->label('Épinglé')
                                    ->inline(false)
                                    ->helperText('Le post sera affiché en haut des listes'),
                            ]),

                        Section::make('Catégories')
                            ->icon('heroicon-m-tag')
                            ->schema([
                                Select::make('categories')
                                    ->label('Catégories')
                                    ->relationship('categories', 'nom')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('nom')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                                        TextInput::make('slug')
                                            ->required()
                                            ->unique(),
                                    ])
                                    ->helperText('Sélectionnez les catégories du post'),

                                Select::make('primary_category')
                                    ->label('Catégorie principale')
                                    ->options(
                                        fn (Get $get): array => PostCategory::whereIn('id', $get('categories') ?? [])
                                            ->pluck('nom', 'id')
                                            ->toArray()
                                    )
                                    ->preload()
                                    ->searchable()
                                    ->visible(fn (Get $get): bool => count($get('categories') ?? []) > 0)
                                    ->helperText('Catégorie principale pour le fil d\'Ariane'),
                            ]),

                        Section::make('Statistiques')
                            ->icon('heroicon-m-chart-bar')
                            ->collapsed()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('views_count')
                                            ->label('Vues')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled()
                                            ->dehydrated(false),

                                        TextInput::make('likes_count')
                                            ->label('J\'aime')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled()
                                            ->dehydrated(false),

                                        TextInput::make('comments_count')
                                            ->label('Commentaires')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled()
                                            ->dehydrated(false),

                                        TextInput::make('reading_time_minutes')
                                            ->label('Temps de lecture')
                                            ->numeric()
                                            ->step(1)
                                            ->suffix('min')
                                            ->helperText('Calculé automatiquement si vide'),
                                    ]),
                            ]),

                        // AJOUT : Section d'aide sur les images
                        // Section::make('📸 Bonnes pratiques images')
                        //     ->icon('heroicon-m-information-circle')
                        //     ->collapsed()
                        //     ->schema([
                        //         View::make('filament.components.image-tips')
                        //             ->visible(fn () => true),
                        //     ]),
                    ]),
            ]);
    }

    /**
     * Crée un composant de prévisualisation de conversion
    //  */
    // private static function createConversionPreview(string $conversion, string $label, string $size): Component
    // {
    //     return View::make('filament.components.conversion-preview')
    //         ->viewData([
    //             'conversion' => $conversion,
    //             'label' => $label,
    //             'size' => $size,
    //         ])
    //         ->visible(fn (Get $get) => ! empty($get('featured')));
    // }
}
