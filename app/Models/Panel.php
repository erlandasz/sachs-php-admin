<?php

namespace App\Models;

use App\SpeakerType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
