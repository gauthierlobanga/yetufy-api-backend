<?php

namespace App\Filament\Resources\TypeDocumentLegals\Schemas;

use App\Models\TypeDocumentLegal;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;

class TypeDocumentLegalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Informations générales')
                    ->icon('heroicon-o-identification')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->helperText('Exemple : RCCM, PATENTE, IFU...'),

                        TextInput::make('nom')
                            ->label('Nom du document')
                            ->required()
                            ->maxLength(255),

                        Select::make('forme_juridique')
                            ->label('Forme juridique concernée')
                            ->options(TypeDocumentLegal::getFormeJuridiqueOptions())
                            ->required()
                            ->helperText('Détermine pour quel type d’activité ce document est exigé'),

                        TextInput::make('autorite_emettrice')
                            ->label('Autorité émettrice')
                            ->maxLength(255)
                            ->placeholder('Ex : GUCE, DGI, Ministère de la Justice'),

                        Toggle::make('est_obligatoire')
                            ->label('Document obligatoire')
                            ->onColor('success')
                            ->offColor('gray')
                            ->columnSpan(1),

                        TextInput::make('ordre')
                            ->label('Ordre d’affichage')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Les petits nombres apparaissent en premier'),
                    ]),

                Section::make('Description')
                    ->icon('heroicon-o-document-text')
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(4)
                            ->maxLength(1000)
                            ->helperText('Décrivez le document et son utilité'),
                    ]),
            ]);
    }
}
