<?php

namespace App\Http\Controllers\Cdc;

use App\Http\Controllers\Controller;

class CdcDashboardController extends Controller
{
    /**
     * Show the CDC dashboard.
     */
    public function index()
    {
        return view('cdc.dashboard');
    }
}
