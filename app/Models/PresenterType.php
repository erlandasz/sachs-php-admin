<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $order
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenterType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenterType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenterType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenterType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenterType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenterType whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PresenterType whereSlug($value)
 *
 * @mixin \Eloquent
 */
class PresenterType extends Model
{
    use LogsActivity;

    protected $connection = 'mysql_hostinger';

    protected $table = 'presenter_types';

    protected $fillable = [
        'name', 'order',
    ];

    public $timestamps = false;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
