<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $name
 * @property string|null $phone_no
 * @property string|null $website
 * @property int|null $founded
 * @property string|null $email
 * @property string|null $logo_name
 * @property string|null $type
 * @property string|null $ticker
 * @property string|null $profile
 * @property string|null $short_profile
 * @property string|null $financial_summary
 * @property string|null $address
 * @property string|null $sector
 * @property string|null $airtableId
 * @property string|null $city
 * @property string|null $country
 * @property string|null $zip
 * @property string|null $street
 * @property string|null $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $airtableLogo
 * @property string|null $nif_airtable_id
 * @property string|null $short_v2
 * @property int $needs_checking
 * @property string|null $cloudinary_url
 * @property-read \App\Models\EventSponsor|\App\Models\EventPresenter|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Event> $eventsAsPresenter
 * @property-read int|null $events_as_presenter_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Event> $eventsAsSponsor
 * @property-read int|null $events_as_sponsor_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Panel> $panel
 * @property-read int|null $panel_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereAirtableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereAirtableLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCloudinaryUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereFinancialSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereFounded($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereLogoName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereNeedsChecking($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereNifAirtableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company wherePhoneNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereProfile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereSector($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereShortProfile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereShortV2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereTicker($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereWebsite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Company whereZip($value)
 *
 * @mixin \Eloquent
 */
class Company extends Model
{
    use LogsActivity;

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'mysql_hostinger';

    protected $fillable = [
        'name',
        'phone_no',
        'founded',
        'website',
        'profile',
        'financial_summary',
        'logo_name',
        'type',
        'address',
        'email',
        'ticker',
        'events',
        'sector',
        'airtableId',
        'city',
        'state',
        'zip',
        'country',
        'short_profile',
    ];

    /**
     * Get the event that owns the panel.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function panel()
    {
        return $this->hasMany(Panel::class);
    }

    public function eventsAsPresenter()
    {
        return $this->belongsToMany(Event::class, 'event_presenter')
            ->using(EventPresenter::class)
            ->withPivot('presenter_type_id');
    }

    public function eventsAsSponsor()
    {
        return $this->belongsToMany(Event::class, 'event_sponsor')
            ->using(EventSponsor::class)
            ->withPivot('sponsor_type_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_presenter')
            ->using(EventPresenter::class)
            ->withPivot('presenter_type_id');
    }
}
