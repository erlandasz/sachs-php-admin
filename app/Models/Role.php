<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method static where(string $string, $slug)
 */
class Role extends Model
{
    protected $connection = 'mysql_hostinger';

    protected $table = 'roles';

    protected $fillable = ['name'];

    public $timestamps = false;

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(PortalUser::class, 'user_role', 'role_id', 'user_id');
    }
}
