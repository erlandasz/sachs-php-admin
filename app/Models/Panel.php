<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
