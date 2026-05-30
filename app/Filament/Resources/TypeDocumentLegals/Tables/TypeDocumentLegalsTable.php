<?php

namespace App\Filament\Resources\TypeDocumentLegals\Tables;

use App\Models\TypeDocumentLegal;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class TypeDocumentLegalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->fontFamily('mono') // Typographie adaptée pour les codes
                    ->copyable() // Permet de copier le code en un clic
                    ->copyMessage('Code copié !')
                    ->tooltip('Cliquez pour copier'),

                TextColumn::make('nom')
                    ->label('Nom du document')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold) // Mise en valeur de l'information principale
                    ->wrap() // Empêche le texte long de casser l'interface
                    ->description(fn (TypeDocumentLegal $record): string => $record->forme_juridique_label ?? ''),

                TextColumn::make('autorite_emettrice')
                    ->label('Autorité')
                    ->icon('heroicon-m-building-library') // Repère visuel rapide
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('forme_juridique')
                    ->label('Forme juridique')
                    ->formatStateUsing(fn (?string $state): string => $state ? TypeDocumentLegal::getFormeJuridiqueLabel($state) : '-')
                    ->badge()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('est_obligatoire')
                    ->label('Obligatoire')
                    ->boolean()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('ordre')
                    ->label('Ordre')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('est_obligatoire')
                    ->label('Statut')
                    ->placeholder('Tous les documents')
                    ->trueLabel('Obligatoires uniquement')
                    ->falseLabel('Facultatifs uniquement'),

                SelectFilter::make('forme_juridique')
                    ->label('Forme juridique')
                    ->options(TypeDocumentLegal::getFormeJuridiqueOptions())
                    ->multiple() // Permet de filtrer sur plusieurs formes en même temps
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])->tooltip('Actions'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('ordre')
            ->defaultSort('ordre', 'asc')
            ->groups([
                Group::make('forme_juridique')
                    ->label('Forme Juridique')
                    ->getTitleFromRecordUsing(fn (TypeDocumentLegal $record): string => $record->forme_juridique_label ?? 'Non définie')
                    ->collapsible(),
            ])
            ->defaultGroup('forme_juridique')
            ->emptyStateHeading('Aucun type de document')
            ->emptyStateDescription('Commencez par ajouter un document légal (ex: RCCM, Patente...).')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
