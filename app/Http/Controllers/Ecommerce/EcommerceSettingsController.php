<?php

namespace App\Http\Controllers\ECommerce;

use App\Http\Controllers\Controller;
use App\Models\EcommerceSetting;
use App\Models\TiktokShop;
use App\Models\ShopeeShop;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
class EcommerceSettingsController extends Controller
{
    public function index(): View
    {
        $tiktokShop = TiktokShop::first();
        $shopeeShop = ShopeeShop::first(); 

        $stockAlertLimit = EcommerceSetting::where('key', 'stock_alert_threshold')->value('value') ?? 10;

        return view('ecommerce.settings.index', compact('tiktokShop','shopeeShop', 'stockAlertLimit'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'stock_alert_threshold' => 'required|integer|min:0',
        ]);

        EcommerceSetting::updateOrCreate(
            ['key' => 'stock_alert_threshold'],
            ['value' => $validated['stock_alert_threshold']]
        );

        return redirect()->route('ecommerce.settings.index')
                         ->with('success', 'Pengaturan berhasil disimpan.');
    }
}