<?php

namespace App\Filament\Resources\PayingCustomerResource\Pages;

use App\Filament\Resources\PayingCustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayingCustomer extends EditRecord
{
    protected static string $resource = PayingCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
