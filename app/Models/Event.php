<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'mysql_hostinger';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'short_name',
        'tagline',
        'starts_at', // Fixed typo: 'starrts_at' → 'starts_at'
        'ends_at',
        'about',
        'networking_text',
        'networking_link',
        'live_link',
        'show_live',
        'bottom_text',
        'how_to_participate',
        'enquiries',
        'after_event',
        'show_event',
        'timezone',
        'slug',
        'location',
        'show_register',
        'afterforum_about',
        'jotform',
        'afterforum_gif_option',
        'afterforum_gif_location',
        'show_supporters',
        'show_presenters',
        'show_exhibitors',
        'show_agenda',
        'show_sponsors',
        'show_panels',
        'show_enquiries',
        'show_participate',
        'show_attendees_tab',
        'show_investors_tab',
        'show_speakers_tab',
        'show_speakers_section',
        'show_presenters_section',
        'show_risings_tab',
        'show_faq_tab',
        'past_event_url',
        'attendees_updated',
        'show_risings_about',
        'show_right',
        'show_venue',
        'pdf_agenda',
        'show_photos',
        'show_floor_plan',
        'show_recordings',
        'is24h',
        'country',
        'reception',
        'in_person_meetings', // Fixed typo: 'in_preson_meetings' → 'in_person_meetings'
        'online_virtual_meetings',
        'airtable_base',
        'airtable_name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'attendees_updated' => 'date',
        'show_live' => 'boolean',
        'after_event' => 'boolean',
        'show_event' => 'boolean',
        'show_register' => 'boolean',
        'afterforum_gif_option' => 'boolean',
        'show_supporters' => 'boolean',
        'show_presenters' => 'boolean',
        'show_exhibitors' => 'boolean',
        'show_agenda' => 'boolean',
        'show_sponsors' => 'boolean',
        'show_panels' => 'boolean',
        'show_enquiries' => 'boolean',
        'show_participate' => 'boolean',
        'show_attendees_tab' => 'boolean',
        'show_investors_tab' => 'boolean',
        'show_speakers_tab' => 'boolean',
        'show_speakers_section' => 'boolean',
        'show_presenters_section' => 'boolean',
        'show_risings_tab' => 'boolean',
        'show_faq_tab' => 'boolean',
        'show_risings_about' => 'boolean',
        'show_right' => 'boolean',
        'show_venue' => 'boolean',
        'show_photos' => 'boolean',
        'show_floor_plan' => 'boolean',
        'show_recordings' => 'boolean',
        'is24h' => 'boolean',
    ];

    public function presenters(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'event_presenter')
            ->using(EventPresenter::class)
            ->withPivot('presenter_type_id');
    }

    public function sponsors(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'event_sponsors')
            ->using(EventSponsor::class)
            ->withPivot('sponsor_type_id');
    }

    public function panels()
    {
        return $this->hasMany(Panel::class);
    }
}
