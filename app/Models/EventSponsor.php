<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class EventSponsor extends Pivot
{
    protected $connection = 'mysql_hostinger';

    protected $table = 'event_sponsors';

    protected $fillable = ['event_id', 'company_id', 'sponsor_type_id'];

    public $timestamps = false;

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function sponsorType(): BelongsTo
    {
        return $this->belongsTo(SponsorType::class);
    }
}
