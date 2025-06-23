<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 
 *
 * @property int $id
 * @property int $event_id
 * @property int $company_id
 * @property int $presenter_type_id
 * @property-read \App\Models\Company $company
 * @property-read \App\Models\Event $event
 * @property-read \App\Models\PresenterType $presenterType
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventPresenter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventPresenter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventPresenter query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventPresenter whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventPresenter whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventPresenter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventPresenter wherePresenterTypeId($value)
 * @mixin \Eloquent
 */
class EventPresenter extends Pivot
{
    protected $connection = 'mysql_hostinger';

    protected $table = 'event_presenter';

    protected $fillable = [
        'event_id',
        'company_id',
        'presenter_type_id',
    ];

    public $timestamps = false;

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function presenterType(): BelongsTo
    {
        return $this->belongsTo(PresenterType::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
