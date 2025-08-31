<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPresentersJob;

class PresenterCronController extends Controller
{
    public function index()
    {
        ProcessPresentersJob::dispatch();

        return response()->json();
    }
}
