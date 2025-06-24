<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string|null $email_verified_at
 * @property int $role
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $last_login
 * @property string|null $email_verification_token
 * @property string $last_activity
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
 * @property-read int|null $roles_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PortalUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PortalUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PortalUser query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PortalUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PortalUser whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PortalUser whereEmailVerificationToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PortalUser whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PortalUser whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PortalUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PortalUser whereLastActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PortalUser whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PortalUser whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PortalUser wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PortalUser whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PortalUser whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PortalUser whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PortalUser extends Model
{
    use LogsActivity;

    protected $connection = 'mysql_hostinger';

    protected $table = 'users';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'email_verification_token',
        'last_login',
        'last_activity',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role', 'user_id', 'role_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
