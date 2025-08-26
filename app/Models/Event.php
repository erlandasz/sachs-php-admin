<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $short_name
 * @property string $name
 * @property string|null $tagline
 * @property \Illuminate\Support\Carbon $starts_at
 * @property \Illuminate\Support\Carbon $ends_at
 * @property string $about
 * @property string|null $networking_text
 * @property string|null $networking_link
 * @property string|null $live_link
 * @property bool $show_live
 * @property string|null $bottom_text
 * @property string|null $how_to_participate
 * @property string|null $enquiries
 * @property bool $after_event
 * @property bool $show_event
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $timezone
 * @property string|null $slug
 * @property string|null $location
 * @property bool $show_register
 * @property string|null $afterforum_about
 * @property string|null $jotform
 * @property bool $afterforum_gif_option
 * @property string|null $afterforum_gif_location
 * @property bool $show_supporters
 * @property bool $show_presenters
 * @property bool $show_exhibitors
 * @property bool $show_agenda
 * @property bool $show_sponsors
 * @property bool $show_panels
 * @property bool $show_enquiries
 * @property bool $show_participate
 * @property bool $show_attendees_tab
 * @property bool $show_investors_tab
 * @property bool $show_speakers_tab
 * @property bool $show_speakers_section
 * @property bool $show_presenters_section
 * @property bool $show_risings_tab
 * @property bool $show_faq_tab
 * @property string|null $past_event_url
 * @property \Illuminate\Support\Carbon|null $attendees_updated
 * @property bool $show_risings_about
 * @property bool|null $show_right
 * @property bool $show_venue
 * @property string|null $pdf_agenda
 * @property bool $show_photos
 * @property bool $show_floor_plan
 * @property bool $show_recordings
 * @property bool $is24h
 * @property string $country
 * @property string|null $reception
 * @property string|null $in_person_meetings
 * @property string|null $online_virtual_meetings
 * @property string|null $airtable_base aitable base in format app...
 * @property string|null $airtable_name airtable table name (usually Registrations)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Panel> $panels
 * @property-read int|null $panels_count
 * @property-read \App\Models\EventSponsor|\App\Models\EventPresenter|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Company> $presenters
 * @property-read int|null $presenters_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Company> $sponsors
 * @property-read int|null $sponsors_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereAbout($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereAfterEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereAfterforumAbout($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereAfterforumGifLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereAfterforumGifOption($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereAirtableBase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereAirtableName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereAttendeesUpdated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereBottomText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereEnquiries($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereHowToParticipate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereInPersonMeetings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereIs24h($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereJotform($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereLiveLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereNetworkingLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereNetworkingText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereOnlineVirtualMeetings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event wherePastEventUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event wherePdfAgenda($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereReception($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShortName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowAgenda($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowAttendeesTab($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowEnquiries($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowExhibitors($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowFaqTab($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowFloorPlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowInvestorsTab($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowLive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowPanels($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowParticipate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowPhotos($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowPresenters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowPresentersSection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowRecordings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowRegister($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowRight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowRisingsAbout($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowRisingsTab($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowSpeakersSection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowSpeakersTab($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowSponsors($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowSupporters($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowVenue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereTagline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Event extends Model
{
    use LogsActivity;

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
        'timezone',
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
