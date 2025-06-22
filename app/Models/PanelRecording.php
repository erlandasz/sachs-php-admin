<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PanelRecording extends Model
{
    protected $connection = 'mysql_hostinger';

    protected $fillable = ['recording_name', 'recording_link'];

    public function panel(): BelongsTo
    {
        return $this->belongsTo(Panel::class);
    }
}
