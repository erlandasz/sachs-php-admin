<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

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
