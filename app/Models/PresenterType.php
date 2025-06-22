<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresenterType extends Model
{
    protected $connection = 'mysql_hostinger';

    protected $table = 'presenter_types';

    protected $fillable = [
        'name', 'order',
    ];

    public $timestamps = false;
}
