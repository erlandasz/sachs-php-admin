<?php

namespace App\Filament\Resources\PortalRoleResource\Pages;

use App\Filament\Resources\PortalRoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPortalRoles extends ListRecords
{
    protected static string $resource = PortalRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
