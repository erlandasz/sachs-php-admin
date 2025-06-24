<?php

namespace App\Filament\Resources\PrintingSettingsResource\Pages;

use App\Filament\Resources\PrintingSettingsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPrintingSettings extends EditRecord
{
    protected static string $resource = PrintingSettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['has_colors'] ?? false) {
            $data = $this->parseRgbFields($data);
        }

        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();

        $data['all_days_color'] = sprintf('rgb(%d, %d, %d)', $record->all_days_r, $record->all_days_g, $record->all_days_b);
        $data['days_1_2_color'] = sprintf('rgb(%d, %d, %d)', $record->days_1_and_2_r, $record->days_1_and_2_g, $record->days_1_and_2_b);
        $data['days_2_3_color'] = sprintf('rgb(%d, %d, %d)', $record->days_2_and_3_r, $record->days_2_and_3_g, $record->days_2_and_3_b);
        $data['day_1_color'] = sprintf('rgb(%d, %d, %d)', $record->day_1_r, $record->day_1_g, $record->day_1_b);
        $data['day_3_color'] = sprintf('rgb(%d, %d, %d)', $record->day_3_r, $record->day_3_g, $record->day_3_b);
        $data['day_2_color'] = sprintf('rgb(%d, %d, %d)', $record->day_2_r, $record->day_2_g, $record->day_2_b);

        return $data;
    }

    protected function parseRgbFields(array $data): array
    {
        $rgbFields = [
            'all_days_color' => ['all_days_r', 'all_days_g', 'all_days_b'],
            'days_1_2_color' => ['days_1_and_2_r', 'days_1_and_2_g', 'days_1_and_2_b'],
            'days_2_3_color' => ['days_2_and_3_r', 'days_2_and_3_g', 'days_2_and_3_b'],
            'day_1_color' => ['day_1_r', 'day_1_g', 'day_1_b'],
            'day_2_color' => ['day_2_r', 'day_2_g', 'day_2_b'],
            'day_3_color' => ['day_3_r', 'day_3_g', 'day_3_b'],
        ];

        foreach ($rgbFields as $colorField => $rgbFields) {
            if (isset($data[$colorField])) {
                preg_match('/rgb\((\d+),\s*(\d+),\s*(\d+)\)/', $data[$colorField], $matches);
                if (count($matches) === 4) {
                    $data[$rgbFields[0]] = $matches[1];
                    $data[$rgbFields[1]] = $matches[2];
                    $data[$rgbFields[2]] = $matches[3];
                }
                unset($data[$colorField]);
            }
        }

        return $data;
    }
}
