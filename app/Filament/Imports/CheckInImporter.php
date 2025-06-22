<?php

namespace App\Filament\Imports;

use App\Models\CheckIn;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class CheckInImporter extends Importer
{
    protected static ?string $model = CheckIn::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('first_name')
                ->label('First Name')->rules(['required']),
            ImportColumn::make('last_name')
                ->label('Last Name'),
            ImportColumn::make('company_name')
                ->label('Company Name'),
            ImportColumn::make('checkin_comment')
                ->label('Comment')->rules(['required']),
            ImportColumn::make('events_attended')
                ->label('Days attended')->helperText('Day numbers separated by comma. e.g. 1,2,3'),
        ];
    }

    public function resolveRecord(): ?CheckIn
    {
        return new CheckIn;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your check in import has completed and '.number_format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
