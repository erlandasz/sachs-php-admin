<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

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
