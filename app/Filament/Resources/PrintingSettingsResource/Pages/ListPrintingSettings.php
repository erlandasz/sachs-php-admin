<?php

namespace App\Filament\Resources\PrintingSettingsResource\Pages;

use App\Filament\Resources\PrintingSettingsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPrintingSettings extends ListRecords
{
    protected static string $resource = PrintingSettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
