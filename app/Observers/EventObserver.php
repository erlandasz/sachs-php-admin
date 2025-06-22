<?php

namespace App\Observers;

use App\Models\Event;
use App\Models\Role;

class EventObserver
{
    /**
     * Handle the Event "created" event.
     */
    public function created(Event $event): void
    {

        $role = new Role([
            'name' => $event->slug,
        ]);
        $role->save();

        $viewOnly = new Role([
            'name' => $event->slug.'-viewOnly',
        ]);
        $viewOnly->save();

        $noMeetings = new Role([
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
