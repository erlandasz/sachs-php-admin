<?php

namespace App\Filament\Resources\PayingCustomerResource\Pages;

use App\Filament\Resources\PayingCustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayingCustomers extends ListRecords
{
    protected static string $resource = PayingCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
