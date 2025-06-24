<?php

namespace App\Models;

use App\Orientation;
use App\PageSize;
use Illuminate\Database\Eloquent\Model;

class PrintingSettings extends Model
{
    protected $fillable = [
        'name',
        'page_size',
        'orientation',
        'page_dimensions',
        'font_family',
        'font_weight',
        'base_font_size',
        'available_width_multiplier',
        'default_colors',
        'row_padding',
        'y_offset',
        'description',
    ];

    protected $casts = [
        'page_size' => PageSize::class,      // If using PHP 8.1 enum
        'orientation' => Orientation::class, // If using PHP 8.1 enum
        'page_dimensions' => 'array',
        'default_colors' => 'array',
    ];
}
