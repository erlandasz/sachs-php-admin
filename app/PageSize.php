<?php

namespace App;

enum PageSize: string
{
    case A6 = 'A6';
    case A5 = 'A5';
    case A4 = 'A4';
    case A3 = 'A3';
    case LETTER = 'LETTER';

    // Optional: Associate each page size with a Filament color
    public function filamentColor(): string
    {
        return match ($this) {
            self::A6 => 'primary',
            self::A5 => 'success',
            self::A4 => 'warning',
            self::A3 => 'danger',
            self::LETTER => 'info',
        };
    }
}
