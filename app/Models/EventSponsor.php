<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $event_id
 * @property int $company_id
 * @property int $sponsor_type_id
 * @property-read \App\Models\Company $company
 * @property-read \App\Models\Event $event
 * @property-read \App\Models\SponsorType $sponsorType
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSponsor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSponsor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSponsor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSponsor whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSponsor whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSponsor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventSponsor whereSponsorTypeId($value)
 *
 * @mixin \Eloquent
 */
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
