<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected $userPlainPassword;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->userPlainPassword = strtolower(bin2hex(random_bytes(4)));
        $data['password'] = Hash::make($this->userPlainPassword);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->notify(new \App\Notifications\AccountCreated($this->userPlainPassword));
    }
}
