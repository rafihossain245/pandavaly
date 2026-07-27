<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the Super Admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('panel.dashboard.index'); // your custom view for super admin dashboard
    }
}
