<?php

namespace App\Http\Controllers;

use App\Models\QAD\Item;
use App\Models\QAD\RequisitionMaster;
use App\Models\QAD\Supplier;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }
}
