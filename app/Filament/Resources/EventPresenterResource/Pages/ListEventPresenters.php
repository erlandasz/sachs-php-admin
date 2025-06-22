<?php

namespace App\Filament\Resources\EventPresenterResource\Pages;

use App\Filament\Resources\EventPresenterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEventPresenters extends ListRecords
{
    protected static string $resource = EventPresenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
