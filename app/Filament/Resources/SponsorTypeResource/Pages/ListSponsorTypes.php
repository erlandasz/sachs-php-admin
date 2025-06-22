<?php

namespace App\Filament\Resources\SponsorTypeResource\Pages;

use App\Filament\Resources\SponsorTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSponsorTypes extends ListRecords
{
    protected static string $resource = SponsorTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
