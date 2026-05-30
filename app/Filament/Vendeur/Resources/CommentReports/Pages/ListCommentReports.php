<?php

namespace App\Filament\Vendeur\Resources\CommentReports\Pages;

use App\Filament\Vendeur\Resources\CommentReports\CommentReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCommentReports extends ListRecords
{
    protected static string $resource = CommentReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
