<?php

namespace App\Observers;

use App\Models\Event;
use App\Models\PortalRole;

class EventObserver
{
    /**
     * Handle the Event "created" event.
     */
    public function created(Event $event): void
    {

        $role = new PortalRole([
            'name' => $event->slug,
        ]);
        $role->save();

        $viewOnly = new PortalRole([
            'name' => $event->slug.'-viewOnly',
        ]);
        $viewOnly->save();

        $noMeetings = new PortalRole([
            'name' => $event->slug.'-noMeetings',
        ]);
        $noMeetings->save();
    }

    /**
     * Handle the Event "updated" event.
     */
    public function updated(Event $event): void
    {
        //
    }

    /**
     * Handle the Event "deleted" event.
     */
    public function deleted(Event $event): void
    {
        //
    }

    /**
     * Handle the Event "restored" event.
     */
    public function restored(Event $event): void
    {
        //
    }

    /**
     * Handle the Event "force deleted" event.
     */
    public function forceDeleted(Event $event): void
    {
        //
    }
}
