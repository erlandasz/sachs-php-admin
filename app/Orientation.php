<?php

namespace App;

enum Orientation: string
{
    case PORTRAIT = 'portrait';
    case LANDSCAPE = 'landscape';

    public function filamentColor(): string
    {
        return match ($this) {
            self::PORTRAIT => 'primary',
            self::LANDSCAPE => 'success',
        };
    }
}
