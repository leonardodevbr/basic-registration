<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // futuramente você adiciona gráficos aqui.
        return view('dashboard');
    }
}
