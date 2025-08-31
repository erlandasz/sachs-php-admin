<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPresentersJob;

class PresenterCronController extends Controller
{
    public function index()
    {
        logger()->debug('Dispatching presenter job');
        ProcessPresentersJob::dispatch();

        return response()->json();
    }
}
