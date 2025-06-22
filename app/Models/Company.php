<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'mysql_hostinger';

    protected $fillable = [
        'name', 'phone_no', 'founded', 'website',
        'profile', 'financial_summary', 'logo_name', 'type',
        'address', 'email', 'ticker', 'events', 'sector',
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
}
