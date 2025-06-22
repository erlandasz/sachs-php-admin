<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayingCustomer extends Model
{
    protected $table = 'paying_customers'; // Adjust to your actual table name

    protected $connection = 'mysql_hostinger';

    protected $fillable = [
        'user_email',
        'role_id',
    ];

    protected $casts = [
        'checked_in' => 'boolean',
        'checked_in_at' => 'datetime',
    ];

    public $timestamps = false; // Set to false if your table doesn't use created_at/updated_at

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
