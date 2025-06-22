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

    public function loadFromAirtable($record): void
    {
        $airtableService = app()->make(AirtableService::class);

        if (empty($record->airtableId)) {
            throw new \Exception('Cannot fetch a person without an Airtable Id');
        }

        $fields = [
            'bio' => 'Biography',
            'job_title' => 'Job Title',
            'companyName' => 'Company Name',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
        ];

        $airtableEntryFields = $airtableService->getEntry($record->airtableId);

        if (isset($airtableEntryFields['Profile Picture'])) {
            $image = $airtableEntryFields['Profile Picture'];
            if (isset($image) && isset($image[0]) && isset($image[0]['url'])) {
                $imageName = $airtableService->extractProfilePicture($image[0]['url']);
                $record->photo = $imageName ?? 'noPic.png';
            }
        }

        foreach ($fields as $property => $fieldName) {
            $record->$property = ! empty($airtableEntryFields[$fieldName]) ? str_replace("\n\n", "\n", $airtableEntryFields[$fieldName]) : '-';
        }

        $record->save();

        Notification::make()
            ->title('Success')
            ->body('Data fetched from Airtable!')
            ->success()
            ->send();
    }
}
