<?php

namespace App\Filament\Resources\PrintingSettingsResource\Pages;

use App\Filament\Resources\PrintingSettingsResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePrintingSettings extends CreateRecord
{
    protected static string $resource = PrintingSettingsResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['has_colors'] ?? false) {
            $data = $this->parseRgbFields($data);
        }

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
