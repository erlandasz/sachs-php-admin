<?php

namespace App\Filament\Resources\PanelResource\Pages;

use App\Filament\Resources\PanelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPanel extends EditRecord
{
    protected static string $resource = PanelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
