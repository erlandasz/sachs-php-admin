<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $company_name
 * @property int|null $checking_in_user_id
 * @property bool $checked_in
 * @property \Illuminate\Support\Carbon|null $checked_in_at
 * @property string $events_attended comma separated event short names
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $checkin_comment comment
 * @property-read \App\Models\PortalUser|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckIn newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckIn newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckIn query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckIn whereCheckedIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckIn whereCheckedInAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckIn whereCheckinComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckIn whereCheckingInUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckIn whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckIn whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckIn whereEventsAttended($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckIn whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckIn whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckIn whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CheckIn whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class CheckIn extends Model
{
    use LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
