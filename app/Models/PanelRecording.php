<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $panel_id
 * @property string|null $recording_name
 * @property string|null $recording_link
 * @property-read \App\Models\Panel $panel
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelRecording newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelRecording newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelRecording query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelRecording whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelRecording wherePanelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelRecording whereRecordingLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PanelRecording whereRecordingName($value)
 * @mixin \Eloquent
 */
class PanelRecording extends Model
{
    protected $connection = 'mysql_hostinger';

    protected $fillable = ['recording_name', 'recording_link'];

    public function panel(): BelongsTo
    {
        return $this->belongsTo(Panel::class);
    }
}
