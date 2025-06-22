<?php

namespace App\Filament\Resources\PresenterTypeResource\Pages;

use App\Filament\Resources\PresenterTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPresenterTypes extends ListRecords
{
    protected static string $resource = PresenterTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
