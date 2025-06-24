<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $user_email
 * @property int $role_id
 * @property string|null $full_name
 * @property string|null $company_name
 * @property bool $checked_in
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon|null $checked_in_at
 * @property int|null $checking_in_user_id
 * @property-read \App\Models\PortalRole|null $role
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayingCustomer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayingCustomer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayingCustomer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayingCustomer whereCheckedIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayingCustomer whereCheckedInAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayingCustomer whereCheckingInUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayingCustomer whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayingCustomer whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayingCustomer whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayingCustomer whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayingCustomer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayingCustomer whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayingCustomer whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayingCustomer whereUserEmail($value)
 *
 * @mixin \Eloquent
 */
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
        return $this->belongsTo(PortalRole::class, 'role_id');
    }
}
