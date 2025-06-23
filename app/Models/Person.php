<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

/**
 * 
 *
 * @property int $id
 * @property string $title
 * @property string $first_name
 * @property string $last_name
 * @property string|null $photo
 * @property string|null $photo_small
 * @property string|null $photo_v2
 * @property string $bio
 * @property string|null $linkedin
 * @property string|null $twitter
 * @property string $job_title
 * @property int|null $company_id
 * @property string|null $airtableId
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $companyName
 * @property int|null $user_id
 * @property bool $needs_checking
 * @property string|null $full_name
 * @property bool|null $show_title
 * @property-read mixed $preview_image_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Panel> $panels
 * @property-read int|null $panels_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person whereAirtableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person whereJobTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person whereLinkedin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person whereNeedsChecking($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person wherePhotoSmall($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person wherePhotoV2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person whereShowTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person whereTwitter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Person whereUserId($value)
 * @mixin \Eloquent
 */
class Person extends Model
{
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'mysql_hostinger';

    public function setFullNameAttribute($value): void
    {
        $this->attributes['full_name'] = trim($value);
    }

    protected $fillable = [
        'title',
        'first_name',
        'last_name',
        'photo',
        'photo_v2',
        'bio',
        'linkedin',
        'twitter',
        'job_title',
        'company_id',
        'airtableId',
        'photo_small',
        'companyName',
        'user_id',
        'needs_checking',
        'full_name',
        'show_title',
    ];

    protected $casts = [
        'needs_checking' => 'boolean',
        'show_title' => 'boolean',
    ];

    public function panels(): BelongsToMany
    {
        return $this->belongsToMany(Panel::class, 'panels_speakers')->withPivot('role');
    }

    public function getPreviewImageUrlAttribute()
    {
        if ($this->photo_v2) {
            return $this->photo_v2;
        }
        if ($this->photo_small) {
            return $this->photo_small;
        }
        if ($this->photo && Storage::disk('local')->exists($this->photo)) {
            return Storage::disk('local')->url($this->photo);
        }

        return asset('noPic.png');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($person) {
            $person->full_name = $person->first_name.' '.$person->last_name;
        });

        static::updating(function ($person) {
            $person->full_name = $person->first_name.' '.$person->last_name;
        });
    }
}
