<?php

namespace App\Filament\Resources\CommentReports\Pages;

use App\Filament\Resources\CommentReports\CommentReportResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCommentReport extends EditRecord
{
    protected static string $resource = CommentReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
