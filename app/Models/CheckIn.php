<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckIn extends Model
{
    protected $connection = 'mysql_hostinger';

    protected $fillable = [
        'first_name',
        'last_name',
        'company_name',
        'checking_in_user_id',
        'checked_in',
        'checked_in_at',
        'events_attended',
        'checkin_comment',
    ];

    public $timestamps = true;

    protected $casts = [
        'checked_in' => 'boolean',
        'checked_in_at' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(PortalUser::class, 'checking_in_user_id');
    }
}
