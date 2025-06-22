<?php

namespace App\Filament\Resources\PanelResource\Pages;

use App\Filament\Resources\PanelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPanels extends ListRecords
{
    protected static string $resource = PanelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
