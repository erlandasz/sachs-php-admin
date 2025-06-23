<?php

namespace App\Filament\Resources\PersonResource\Pages;

use App\Filament\Resources\PersonResource;
use App\Services\AirtableService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPerson extends EditRecord
{
    protected static string $resource = PersonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('loadFromAirtable')
                ->label('Fetch Airtable')
                ->action(function ($record) {
                    $this->loadFromAirtable($record);
                })
                ->visible(fn ($record) => ! empty($record->airtableId)),
        ];
    }

    public function loadFromAirtable(Person $record): void
    {
        $airtableService = app()->make(AirtableService::class);
        $airtableService->loadSpeaker($record);

        Notification::make()
            ->title('Success')
            ->body('Data fetched from Airtable!')
            ->success()
            ->send();
    }
}
