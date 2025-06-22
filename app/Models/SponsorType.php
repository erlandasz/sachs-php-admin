<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SponsorType extends Model
{
    protected $connection = 'mysql_hostinger';

    public $timestamps = false;

    protected $fillable = ['name', 'header_singular', 'header_plural', 'display_order'];
}
