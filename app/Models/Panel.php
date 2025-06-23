<?php

namespace App\Models;

use App\SpeakerType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $room
 * @property string $type
 * @property string $starts_at
 * @property string $ends_at
 * @property int|null $company_id
 * @property int|null $event_id
 * @property string $day
 * @property string|null $recording
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $track
 * @property SpeakerType $role
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Company> $companies
 * @property-read int|null $companies_count
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Event|null $event
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Person> $person
 * @property-read int|null $person_count
 * @property-read \App\Models\PanelRecording|null $recording_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Person> $speaker
 * @property-read int|null $speaker_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Panel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Panel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Panel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Panel whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Panel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Panel whereDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Panel whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Panel whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Panel whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Panel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Panel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Panel whereRecording($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Panel whereRoom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Panel whereStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Panel whereTrack($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Panel whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Panel whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Panel extends Model
{
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'mysql_hostinger';

    protected $fillable = [
        'name', 'phone_no', 'website', 'founded', 'email', 'logo_name', 'type',
        'ticker', 'profile', 'short_profile', 'financial_summary', 'address',
        'sector', 'airtableId', 'city', 'country', 'zip', 'street', 'state',
        'airtableLogo', 'nif_airtable_id', 'short_v2', 'needs_checking',
        'cloudinary_url',
    ];

    protected $casts = [
        'role' => SpeakerType::class,
    ];

    /**
     * Get the event that owns the panel.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the event that owns the panel.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the event that owns the panel.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function person()
    {
        return $this->belongsToMany(Person::class, 'panels_speakers')->withPivot('role');
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'panel_company')
            ->withPivot('order')
            ->orderByRaw('ISNULL(panel_company.order), panel_company.order ASC')
            ->orderBy('companies.name', 'ASC');
    }

    public function speaker(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'panels_speakers')
            ->wherePivot('role', SpeakerType::Speaker->value)
            ->with('company:id,name')
            ->orderBy('first_name', 'asc');
    }

    public function recording_url(): HasOne
    {
        return $this->hasOne(PanelRecording::class);
    }
}
