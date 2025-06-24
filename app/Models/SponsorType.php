<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $name
 * @property string $header_singular
 * @property string $header_plural
 * @property int $display_order
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SponsorType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SponsorType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SponsorType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SponsorType whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SponsorType whereHeaderPlural($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SponsorType whereHeaderSingular($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SponsorType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SponsorType whereName($value)
 *
 * @mixin \Eloquent
 */
class SponsorType extends Model
{
    use LogsActivity;

    protected $connection = 'mysql_hostinger';

    public $timestamps = false;

    protected $fillable = ['name', 'header_singular', 'header_plural', 'display_order'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
