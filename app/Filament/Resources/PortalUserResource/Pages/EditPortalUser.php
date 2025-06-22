<?php

namespace App\Filament\Resources\PortalUserResource\Pages;

use App\Filament\Resources\PortalUserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPortalUser extends EditRecord
{
    protected static string $resource = PortalUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
