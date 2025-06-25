<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected $userPlainPassword;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->userPlainPassword = Str::random(12);
        $data['password'] = Hash::make($this->userPlainPassword);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->notify(new \App\Notifications\AccountCreated($this->userPlainPassword));
    }
}
