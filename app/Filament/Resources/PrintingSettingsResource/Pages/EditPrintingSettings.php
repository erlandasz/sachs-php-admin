<?php

namespace App\Filament\Resources\PrintingSettingsResource\Pages;

use App\Filament\Resources\PrintingSettingsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPrintingSettings extends EditRecord
{
    protected static string $resource = PrintingSettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
