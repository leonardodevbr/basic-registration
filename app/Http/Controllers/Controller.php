<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\View;
use Jenssegers\Agent\Agent;

abstract class Controller
{
    protected Agent $agent;

    public function __construct(
        Agent $agent
    )
    {
        $this->agent = $agent;

        View::share(compact('agent'));
    }
}
