<?php

namespace App\Http\Controllers;

use App\Models\StorageArea;
use App\Models\IngredientExpiry;
use App\Models\IncomingItem;
use Illuminate\Http\Request;

class InwardDashboardController extends Controller
{
    public function index()
    {
        $storageAreas = StorageArea::all();
        $expiries = IngredientExpiry::all();
        $incoming = IncomingItem::all();

        return view('dashboard.inward', compact('storageAreas', 'expiries', 'incoming'));
    }
}