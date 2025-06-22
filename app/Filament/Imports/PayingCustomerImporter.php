<?php

namespace App\Filament\Imports;

use App\Models\PayingCustomer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class PayingCustomerImporter extends Importer
{
    protected static ?string $model = PayingCustomer::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('user_email')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255']),
            ImportColumn::make('role_id')
                ->requiredMapping()
                ->rules(['required', 'integer']),
        ];
    }

    public function resolveRecord(): ?PayingCustomer
    {
        return new PayingCustomer();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your paying customer import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
