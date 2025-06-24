<?php

namespace App\Filament\Resources\PortalRoleResource\Pages;

use App\Filament\Resources\PortalRoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPortalRole extends EditRecord
{
    protected static string $resource = PortalRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
