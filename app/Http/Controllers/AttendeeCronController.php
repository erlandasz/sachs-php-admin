<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessAttendeesJob;
use Illuminate\Http\JsonResponse;

class AttendeeCronController extends Controller
{
    public function index(): JsonResponse
    {

        ProcessAttendeesJob::dispatch();

        return response()->json();
    }
}
