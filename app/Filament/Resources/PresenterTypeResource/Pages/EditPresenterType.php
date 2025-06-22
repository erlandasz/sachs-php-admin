<?php

namespace App\Filament\Resources\PresenterTypeResource\Pages;

use App\Filament\Resources\PresenterTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPresenterType extends EditRecord
{
    protected static string $resource = PresenterTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
