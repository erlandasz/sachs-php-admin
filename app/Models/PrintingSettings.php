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
        'has_colors',
        'is_default',

        // DB columns for RGB values
        'all_days_r', 'all_days_g', 'all_days_b',
        'days_1_and_2_r', 'days_1_and_2_g', 'days_1_and_2_b',
        'days_2_and_3_r', 'days_2_and_3_g', 'days_2_and_3_b',
        'day_1_r', 'day_1_g', 'day_1_b',
        'day_2_r', 'day_2_g', 'day_2_b',
        'day_3_r', 'day_3_g', 'day_3_b',
    ];

    protected static function booted(): void
    {
        static::saving(function ($model) {
            if ($model->is_default) {
                self::where('id', '!=', $model->id)->update(['is_default' => false]);
            }

            if ($model->has_colors) {
                $model->parseAndSaveRgb('all_days_color', 'all_days_r', 'all_days_g', 'all_days_b');
                $model->parseAndSaveRgb('days_1_2_color', 'days_1_and_2_r', 'days_1_and_2_g', 'days_1_and_2_b');
                $model->parseAndSaveRgb('days_2_3_color', 'days_2_and_3_r', 'days_2_and_3_g', 'days_2_and_3_b');
                $model->parseAndSaveRgb('day_1_color', 'day_1_r', 'day_1_g', 'day_1_b');
                $model->parseAndSaveRgb('day_2_color', 'day_2_r', 'day_2_g', 'day_2_b');
                $model->parseAndSaveRgb('day_3_color', 'day_3_r', 'day_3_g', 'day_3_b');
            }
        });
    }

    protected $casts = [
        'page_size' => PageSize::class,
        'orientation' => Orientation::class,
        'page_dimensions' => 'array',
        'default_colors' => 'array',
        'is_default' => 'bool',
    ];

    public function getAllDaysColor(): array
    {
        return [
            'r' => $this->all_days_r,
            'g' => $this->all_days_g,
            'b' => $this->all_days_b,
        ];
    }

    public function getDays1And2Color(): array
    {
        return [
            'r' => $this->days_1_and_2_r,
            'g' => $this->days_1_and_2_g,
            'b' => $this->days_1_and_2_b,
        ];
    }

    public function getDays2And3Color(): array
    {
        return [
            'r' => $this->days_2_and_3_r,
            'g' => $this->days_2_and_3_g,
            'b' => $this->days_2_and_3_b,
        ];
    }

    public function getDay1Color(): array
    {
        return [
            'r' => $this->day_1_r,
            'g' => $this->day_1_g,
            'b' => $this->day_1_b,
        ];
    }

    public function getDay2Color(): array
    {
        return [
            'r' => $this->day_2_r,
            'g' => $this->day_2_g,
            'b' => $this->day_2_b,
        ];
    }

    public function getDay3Color(): array
    {
        return [
            'r' => $this->day_3_r,
            'g' => $this->day_3_g,
            'b' => $this->day_3_b,
        ];
    }

    protected function parseAndSaveRgb(string $colorField, string $rField, string $gField, string $bField): void
    {
        if ($this->offsetExists($colorField)) {
            preg_match('/rgb\((\d+),\s*(\d+),\s*(\d+)\)/', $this->{$colorField}, $matches);
            if (count($matches) === 4) {
                $this->{$rField} = $matches[1];
                $this->{$gField} = $matches[2];
                $this->{$bField} = $matches[3];
            }
            $this->offsetUnset($colorField); // Properly removes from attributes
        }
    }
}
