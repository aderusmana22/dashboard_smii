<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\Ecommerce\EcommerceSalesController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QAD\InventoryController;
use App\Http\Controllers\QAD\ProductionController;
use App\Http\Controllers\QAD\SalesController;
use App\Http\Controllers\Role\PermissionController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\SalesByBrandReports;
use App\Http\Controllers\UserController;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardSalesController;
use App\Http\Controllers\StandardBudgetController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\MarshoDepartmentController;
use App\Http\Controllers\MarshoUserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\HSE\SafetyBoardController;
use App\Http\Controllers\LaporanKecelakaanController;
use App\Http\Controllers\EditorImageController;
use App\Http\Controllers\Ecommerce\DashboardEcommerceController;
use App\Http\Controllers\Ecommerce\ProductController;
use App\Http\Controllers\EmailApprovalController;
use App\Http\Controllers\Ecommerce\EcommerceSettingsController;
use App\Http\Controllers\TiktokController;
use App\Http\Controllers\TiktokShop\TiktokProductController;
use App\Http\Controllers\Ecommerce\OrderListController;
use App\Http\Controllers\Ecommerce\SalesDashboardController;
use App\Http\Controllers\Shopee\ShopeeController;
use App\Http\Controllers\Ecommerce\ShopeeOrderController;
use App\Http\Controllers\Ecommerce\ProductTonnageController;
use App\Http\Controllers\PPIC\ForecastController;
use App\Http\Controllers\OilController;
use App\Http\Controllers\InwardDashboardController;
use App\Http\Controllers\OilUtilityGasInputController;
use App\Http\Controllers\OilUtilityGasConfigController;
use App\Http\Controllers\OilBatchRefineryController;
use App\Http\Controllers\OilBatchRefineryInputController;
use App\Http\Controllers\OilBatchRefineryConfigController;
use App\Http\Controllers\InputStationController; 

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    SEOMeta::setTitle('Intra Dashboard SMII');
    return redirect()->route('login');
});

Route::middleware('auth', 'redirect.if.role')->group(function () {
    /* Dashboard */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard-finance', [DashboardController::class, 'index-finance'])->name('dashboard-finance');
    Route::get('/api/requisitions/{year}', [DashboardController::class, 'getRequisitionsByYear'])->name('dashboard.requisitions.byYear');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile', [UserController::class, 'updateProfile'])->name('profile.updates');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/get-data-master', [UserController::class, 'getDataMaster'])->name('get.master');


    // sales
    Route::get('/dashboard-sales', [DashboardSalesController::class, 'showMapDashboard'])->name('dashboard.dashboardSales');
    Route::get('/api/sales-data', [DashboardSalesController::class, 'getSalesData'])->name('api.sales.data');

    /*Inventory*/
    /*get wsa inventory*/
    Route::get('dashboard/inventory/wsa', [InventoryController::class, 'getDashboardInventory'])->name('dashboard.inventory.wsa');
    /*inventory routes*/
    Route::get('dashboard/inventory', [InventoryController::class, 'index'])->name('dashboard.inventory');


    /*Production*/
    /*get wsa production*/
    Route::post('dashboard/production/get', [ProductionController::class, 'getProductions'])->name('dashboard.production.wsa');
    /*production routes*/
    Route::get('production', [ProductionController::class, 'index'])->name('data.production');


    /*Sales*/
    /*get wsa sales*/
    Route::post('dashboard/sales/get', [SalesController::class, 'getSalesDashboard'])->name('dashboard.sales.wsa');
    /*sales routes*/
    Route::get('sales', [SalesController::class, 'index'])->name('data.sales');

    /* Standard Budget */
    Route::prefix('standard-budgets')->name('standard-budgets.')->group(function () {
        Route::get('/', [StandardBudgetController::class, 'index'])->name('index');
        Route::post('/', [StandardBudgetController::class, 'store'])->name('store');
        Route::get('/{standardBudget}/edit', [StandardBudgetController::class, 'edit'])->name('edit');
        Route::put('/{standardBudget}', [StandardBudgetController::class, 'update'])->name('update');
        Route::delete('/{standardBudget}', [StandardBudgetController::class, 'destroy'])->name('destroy');

        // Route::get('/import', [StandardBudgetController::class, 'showImportForm'])->name('import.form'); // HAPUS BARIS INI
        Route::post('/import', [StandardBudgetController::class, 'importExcel'])->name('import.excel');
    });
    Route::get('/standard-budgets/sample/download', function () {
        $filePath = storage_path('app/files/sample.xlsx');

        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->download($filePath, 'template_budget.xlsx');
    })->name('standard-budgets.download-sample');




    /*standard production*/
    Route::get('dashboard/standard-production/', [ProductionController::class, 'standardProduction'])->name('dashboard.production.standard');
    Route::post('dashboard/standard-production/', [ProductionController::class, 'storeStandardProductions'])->name('dashboard.production.standard.store');
    Route::put('dashboard/standard-production/update/{standardproduction}', [ProductionController::class, 'updateStandardProductions'])->name('dashboard.standard-production.update');
    Route::delete('dashboard/standard-production/destroy/{standardproduction}', [ProductionController::class, 'destroyStandardProductions'])->name('dashboard.standard-production.destroy');

    /*standard shipment*/
    Route::post('dashboard/standard-shipment', [SalesController::class, 'getShipment'])->name('dashboard.standardshipment.wsa');

    Route::get('dashboard/standard-shipment', [SalesController::class, 'shipmentindex'])->name('dashboard.shipmentindex');
    Route::post('dashboard/standard-shipment/store', [SalesController::class, 'shipmentstore'])->name('dashboard.shipmentstore');
    Route::put('dashboard/standard-shipment/{standardshipment}', [SalesController::class, 'shipmentupdate'])->name('dashboard.shipmentupdate');
    Route::delete('dashboard/standard-shipment/{standardshipment}', [SalesController::class, 'shipmentdelete'])->name('dashboard.shipmentdelete');

    /*standard warehouse*/
    Route::get('dashboard/standard-warehouse', [InventoryController::class, 'warehouseindex'])->name('dashboard.warehouseindex');
    Route::post('dashboard/standard-warehouse', [InventoryController::class, 'warehousestore'])->name('dashboard.warehousestore');
    Route::put('dashboard/standard-warehouse/{standardwarehouse}', [InventoryController::class, 'warehouseupdate'])->name('dashboard.warehouseupdate');
    Route::delete('dashboard/standard-warehouse/{standardwarehouse}', [InventoryController::class, 'warehousedelete'])->name('dashboard.warehousedelete');

    Route::post('dashboard/inventory/wsa', [InventoryController::class, 'getDashboardInventory'])->name('dashboard.inventory.wsa');
    /*inventory routes*/
    Route::get('dashboard/inventory', [InventoryController::class, 'index'])->name('dashboard.inventory');

    /*Dashboard Warehouse*/
    Route::get('dashboard-warehouse', function () {
        return view("dashboard.dashboardWarehouse");
    })->name('dashboard.dashboardWarehouse');
    /*Dashboard Production*/
    Route::get('dashboard/dashboard-production', [ProductionController::class, 'dashboardProductionIndex'])->name('dashboard.dashboardProduction');

    Route::prefix('dashboard/safety-board')->name('dashboard.safety-board.')->group(function () {
        Route::get('/', [SafetyBoardController::class, 'index'])->name('index');
        Route::get('/api/safety-data', [SafetyBoardController::class, 'getSafetyData']); // For AJAX calls

    });


    Route::prefix('accidents-report')->name('accidents-report.')->group(function () {

        // Halaman utama yang akan menampilkan tabel DataTables
        Route::get('/', [LaporanKecelakaanController::class, 'index'])->name('index');

        // Endpoint khusus untuk DataTables mengambil data via AJAX (Server-Side)
        Route::get('/data', [LaporanKecelakaanController::class, 'getData'])->name('data');

        // Rute untuk membuat laporan baru
        Route::get('/create', [LaporanKecelakaanController::class, 'create'])->name('create');
        Route::post('/', [LaporanKecelakaanController::class, 'store'])->name('store');

        // =================================================================
        // PERUBAHAN: Menggunakan {laporan:nomor_form} untuk semua rute
        // yang memerlukan model LaporanKecelakaan.
        // =================================================================

        // Rute untuk melihat detail laporan
        Route::get('/{laporan:nomor_form}', [LaporanKecelakaanController::class, 'show'])->name('show');

        // Rute untuk merevisi laporan yang ditolak
        Route::get('/{laporan:nomor_form}/revise', [LaporanKecelakaanController::class, 'revise'])->name('revise');

        // Rute untuk aksi persetujuan dan penolakan (akan dipanggil via AJAX)
        Route::post('/{laporan:nomor_form}/approve', [LaporanKecelakaanController::class, 'approve'])->name('approve');
        Route::post('/{laporan:nomor_form}/reject', [LaporanKecelakaanController::class, 'reject'])->name('reject');
    });

    Route::post('editor/upload-image', [EditorImageController::class, 'store'])->name('editor.upload.image');

    /*Dashboard Inventory*/
    Route::get('dashboard-inventory', [InventoryController::class, 'dashboardInventory'])->name('dashboard.dashboardInventory');


    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/markAllAsRead', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::delete('/notifications/clear', [NotificationController::class, 'clearAll'])->name('notifications.clear');
    Route::get('/notifications/count', function () {
        return response()->json(['count' => auth()->user()->unreadNotifications->count()]);
    })->name('notifications.count');


    // Rute untuk Job Kanban
    Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
    Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');
    Route::patch('/jobs/{job}/schedule', [JobController::class, 'setScheduled'])->name('jobs.schedule');
    Route::patch('/jobs/{job}/prepare', [JobController::class, 'setPreparation'])->name('jobs.prepare');
    Route::patch('/jobs/{job}/start', [JobController::class, 'start'])->name('jobs.start');

    Route::post('/jobs/{job}/forward', [JobController::class, 'forward'])->name('jobs.forward');
    Route::patch('/jobs/{job}/complete', [JobController::class, 'complete'])->name('jobs.complete');
    Route::post('/jobs/{job}/close', [JobController::class, 'close'])->name('jobs.close');

    Route::patch('/jobs/{job}/change-status', [JobController::class, 'changeStatus'])->name('jobs.changeStatus');
    Route::get('/jobs/{job}/details', [JobController::class, 'showDetails'])->name('jobs.details');

    Route::patch('/jobs/{job}/cancel', [JobController::class, 'cancel'])->name('jobs.cancel');

    // Rute untuk mengelola Resources (Area dan Departemen)
    // Route ini sudah menangani GET (index) dan POST (store)
    Route::resource('areas', AreaController::class)->except(['create', 'show', 'edit']);

    // Biasanya, resource controller sudah mencakup ini, tetapi pastikan:
    Route::put('/areas/{area}', [AreaController::class, 'update'])->name('areas.update');
    Route::delete('/areas/{area}', [AreaController::class, 'destroy'])->name('areas.destroy');

    Route::resource('marsho-departments', MarshoDepartmentController::class)->except(['show', 'edit', 'create']);

    // Rute untuk Activity Log
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    // Rute untuk melihat log spesifik per Job
    Route::get('/jobs/{job}/activity-logs', [ActivityLogController::class, 'showForJob'])->name('jobs.activity-logs.show');

    Route::get('/marsho-users', [MarshoUserController::class, 'index'])->name('marsho-users.index');
    Route::post('/marsho-users', [MarshoUserController::class, 'store'])->name('marsho-users.store');

    //export kanban
    Route::prefix('reports')->name('reports.')->group(function () {
        // == ROUTE BARU UNTUK EKSPOR MARSHO JOBS ==
        Route::get('/marsho-jobs', [ReportController::class, 'showJobsExportPage'])->name('marsho-jobs.page');

        Route::get('/marsho-jobs/export', [ReportController::class, 'exportMarshoJobs'])->name('marsho-jobs.export');
    });

    // Route to display the initial report page
    Route::get('dashboard/sales-by-brand-report', [SalesByBrandReports::class, 'show'])->name('reports.sales.byBrand');

    // Route for DataTables to fetch data via AJAX
    Route::get('dashboard/sales-by-brand-report/data', [SalesByBrandReports::class, 'fetchData'])->name('reports.sales.byBrand.data');

    Route::get('/reports/sales-by-brand-export', [SalesByBrandReports::class, 'exportExcel'])->name('reports.sales.byBrand.export');

    Route::get('/ecommerce', [DashboardEcommerceController::class, 'index'])->name('dashboard.ecommerce');
    Route::get('/ecommerce/products', [ProductController::class, 'index'])
        ->middleware(['auth'])->name('ecommerce.products.index');
    // Rute untuk Halaman Data Penjualan E-Commerce
    Route::get('/ecommerce/sales', [SalesDashboardController::class, 'index'])->name('ecommerce.sales.index');
    Route::get('/ecommerce/settings', [EcommerceSettingsController::class, 'index'])->name('ecommerce.settings.index');
    Route::post('/ecommerce/settings', [EcommerceSettingsController::class, 'update'])->name('ecommerce.settings.update');
    Route::get('/ecommerce/tokopedia/orders-data', [SalesDashboardController::class, 'getPaginatedOrders'])->name('ecommerce.tokopedia.orders.data');
    Route::get('/ecommerce/shopee/orders-data', [SalesDashboardController::class, 'getPaginatedShopeeOrders'])->name('ecommerce.shopee.orders.data');


    Route::get('/ecommerce/products', [ProductController::class, 'index'])->name('ecommerce.products.index');
    Route::post('/ecommerce/products/sync', [ProductController::class, 'sync'])->name('ecommerce.products.sync');
    Route::post('/ecommerce/products/{product}/stock', [ProductController::class, 'updateStock'])->name('ecommerce.products.stock.update');


    Route::prefix('ecommerce')->name('ecommerce.')->group(function () {
        // ... rute produk Anda yang lain

        // Rute untuk Halaman Tonnage Mapper
        Route::get('/products/tonnage', [ProductTonnageController::class, 'index'])->name('products.tonnage.index');
        Route::post('/products/tonnage', [ProductTonnageController::class, 'store'])->name('products.tonnage.store');
    });

    // === BAGIAN YANG PERLU DIPERBAIKI / DITAMBAHKAN ===
    // Route untuk memicu sinkronisasi TikTok
    Route::post('ecommerce/products/sync-tiktok', [ProductController::class, 'syncTiktok'])->name('ecommerce.products.sync.tiktok');

    // Route untuk memicu sinkronisasi Shopee (INI YANG HILANG)
    Route::post('ecommerce/products/sync-shopee', [ProductController::class, 'syncShopee'])->name('ecommerce.products.sync.shopee');
    // =================================================

    // Route untuk memperbarui stok produk master
    Route::post('ecommerce/products/{product}/update-stock', [ProductController::class, 'updateStock'])->name('products.stock.update');

    // Route AJAX untuk statistik kartu
    Route::get('/ecommerce/dashboard/tokopedia-stats', [DashboardEcommerceController::class, 'fetchTokopediaStats'])->name('ecommerce.dashboard.tokopedia_stats');

    // == ROUTE BARU UNTUK AJAX KARTU SHOPEE ==
    Route::get('/ecommerce/dashboard/shopee-stats', [DashboardEcommerceController::class, 'fetchShopeeStats'])->name('ecommerce.dashboard.shopee_stats');

    Route::get('/ecommerce/dashboard/tokopedia-stats', [DashboardEcommerceController::class, 'fetchTokopediaStats'])->name('ecommerce.dashboard.tokopedia_stats');
    Route::get('/ecommerce/sales/fetch-data', [SalesDashboardController::class, 'fetchSalesData'])->name('ecommerce.sales.fetch_data');
    Route::get('/ecommerce/dashboard/chart-data', [DashboardEcommerceController::class, 'fetchChartData'])->name('ecommerce.dashboard.chart_data');
    Route::post('/ecommerce/products/{product}/update-price', [ProductController::class, 'updatePrice'])
        ->name('ecommerce.products.price.update');
    Route::prefix('tiktok')->name('tiktok.')->group(function () {
        Route::get('/auth', [TiktokController::class, 'redirectToAuth'])->name('auth');
        Route::get('/callback', [TiktokController::class, 'handleCallback'])->name('callback');
        Route::delete('/disconnect', [TiktokController::class, 'disconnect'])->name('disconnect');
    });
    Route::prefix('ecommerce/products')->name('ecommerce.products.')->group(function () {
        // ======================================================================
        // === PASTIKAN BARIS INI ADA DAN TIDAK ADA SALAH KETIK ===
        // ======================================================================
        Route::post('/{product}/add-stock', [ProductController::class, 'addStock'])->name('add.stock');
        // ======================================================================

    });
    Route::prefix('ecommerce')->name('ecommerce.')->group(function () {
        // ... route ecommerce lainnya

        // == ROUTE BARU UNTUK MODAL AKSI CEPAT ==
        Route::get('/dashboard/modal-data', [App\Http\Controllers\Ecommerce\DashboardEcommerceController::class, 'fetchModalData'])->name('dashboard.modalData');

    });

    Route::get('/dashboard/ecommerce/fetch-top-products', [DashboardEcommerceController::class, 'fetchTopProducts'])->name('dashboard.ecommerce.fetchTopProducts');
    Route::get('/dashboard/ecommerce/fetch-recent-transactions', [DashboardEcommerceController::class, 'fetchRecentTransactions'])->name('dashboard.ecommerce.fetchRecentTransactions');

    Route::prefix('shopee')->name('shopee.')->group(function () {
        Route::get('/auth', [ShopeeController::class, 'redirectToAuth'])->name('auth');
        Route::get('/callback', [ShopeeController::class, 'handleCallback'])->name('callback');
        Route::post('/disconnect', [ShopeeController::class, 'disconnect'])->name('disconnect');
    });

    Route::post('/ecommerce/shopee/orders/sync', [ShopeeOrderController::class, 'syncOrders'])
        ->name('ecommerce.shopee.orders.sync');
    // Route untuk sinkronisasi (pastikan sudah ada)\
    Route::post('/ecommerce/tiktok/orders/sync', [OrderListController::class, 'syncOrders'])->name('ecommerce.tiktok.orders.sync');


    Route::get('/tiktok/orders', [OrderListController::class, 'index'])->name('tiktok.orders.data');

    Route::post('/ecommerce/tiktok/sync', [OrderListController::class, 'syncOrders'])->name('ecommerce.tiktok.sync');

    // TESTING
    Route::get('/tiktok-debug', [TiktokController::class, 'debugApiCall']);

    Route::prefix('testing')->name('testing.')->group(function () {
        Route::get('/email/request', [\App\Http\Controllers\DevTestingController::class, 'previewEmailRequest'])->name('email.request');
        Route::get('/email/success', [\App\Http\Controllers\DevTestingController::class, 'previewSuccess'])->name('email.success');
        Route::get('/email/invalid', [\App\Http\Controllers\DevTestingController::class, 'previewInvalid'])->name('email.invalid');
        Route::get('/email/reject-form', [\App\Http\Controllers\DevTestingController::class, 'previewRejectForm'])->name('email.reject_form');
    });

    Route::prefix('ppic')->name('ppic.')->group(function () {
        Route::get('forecast', [ForecastController::class, 'index'])->name('forecast.index');
        Route::get('forecast/data', [ForecastController::class, 'fetchData'])->name('forecast.data');
        Route::post('forecast/import', [ForecastController::class, 'import'])->name('forecast.import');
        Route::get('forecast/template', [ForecastController::class, 'downloadTemplate'])->name('forecast.template');
    });

    Route::get('/oil-monitoring', [OilController::class, 'index'])->name('oil.index');
    Route::get('/oil/load-component/{componentName}', [OilController::class, 'loadComponent'])->name('oil.loadComponent');
    Route::get('/oil/get-tank-data', [OilController::class, 'getTankData'])->name('oil.getTankData');
    Route::get('/oil/get-refinery-data', [OilController::class, 'getRefineryData'])->name('oil.getRefineryData');
    Route::get('/oil/get-fat-blend-data', [OilController::class, 'getFatBlendData'])->name('oil.getFatBlendData');
    Route::get('/oil/get-yard-1t-data', [OilController::class, 'getYard1tData'])->name('oil.getYard1tData');
    Route::get('/oil/get-bleached-oil-data', [OilController::class, 'getBleachedOilData'])->name('oil.getBleachedOilData');
    Route::get('/oil/get-packing-data', [OilController::class, 'getPackingData'])->name('oil.getPackingData');
    Route::get('/oil/get-current-stock-data', [OilController::class, 'getCurrentStockData'])->name('oil.getCurrentStockData');

    Route::prefix('oil/utility-gas')->name('utility.gas.')->group(function () {
        Route::get('/data', [OilController::class, 'getUtilityGasData'])->name('data');
        Route::get('/input', [OilUtilityGasInputController::class, 'index'])->name('input');
        Route::post('/store', [OilUtilityGasInputController::class, 'store'])->name('store');
        Route::get('/logs', [OilUtilityGasInputController::class, 'logs'])->name('logs');

        Route::get('/config', [OilUtilityGasConfigController::class, 'index'])->name('config.index');
        Route::post('/config', [OilUtilityGasConfigController::class, 'store'])->name('config.store');
        Route::put('/config/{id}', [OilUtilityGasConfigController::class, 'update'])->name('config.update');
        Route::delete('/config/{id}', [OilUtilityGasConfigController::class, 'destroy'])->name('config.destroy');
    });


    Route::prefix('oil/batch-refinery')->name('oil.batch_refinery.')->group(function () {
        Route::get('/', [OilBatchRefineryController::class, 'index'])->name('index');
        Route::get('/data', [OilBatchRefineryController::class, 'getData'])->name('data');
        Route::get('/logs', [OilBatchRefineryController::class, 'logs'])->name('logs');

        Route::get('/input', [OilBatchRefineryInputController::class, 'index'])->name('input');
        Route::post('/input/start', [OilBatchRefineryInputController::class, 'startSession'])->name('input.start');
        Route::post('/input/store', [OilBatchRefineryInputController::class, 'storeStep'])->name('input.store');
        Route::get('/input/reset', [OilBatchRefineryInputController::class, 'resetSession'])->name('input.reset');

          Route::post('/input/store-full', [OilBatchRefineryInputController::class, 'storeFull'])->name('input.store_full');

        Route::get('/config', [OilBatchRefineryConfigController::class, 'index'])->name('config.index');
        Route::post('/config', [OilBatchRefineryConfigController::class, 'store'])->name('config.store');
        Route::put('/config/{id}', [OilBatchRefineryConfigController::class, 'update'])->name('config.update');
        Route::delete('/config/{id}', [OilBatchRefineryConfigController::class, 'destroy'])->name('config.destroy');
    });

    Route::get('/oil-input', [InputStationController::class, 'index'])->name('oil.input_station.index');



    Route::get('/inward-dashboard', [InwardDashboardController::class, 'index'])->name('inward.dashboard');
});


// Route untuk menampilkan halaman utama


// approval email
Route::group(['as' => 'email-approval.', 'prefix' => 'email-approval'], function () {
    Route::get('/approve/{token}', [EmailApprovalController::class, 'approve'])->name('approve');
    Route::get('/reject/{token}', [EmailApprovalController::class, 'showRejectForm'])->name('show-reject-form');
    Route::post('/reject', [EmailApprovalController::class, 'reject'])->name('reject');
});


/*Dashboard Route Get Filter*/
Route::get('/get-dashboard-production', [ProductionController::class, 'dashboardProduction']);
Route::get('/bar-data', [ProductionController::class, 'getBarData']);
Route::get('/data-filter', [ProductionController::class, 'filterData']);
Route::get('/year-data', [ProductionController::class, 'getBarDataByYear']);



/* Dashboard Route Get Filter Warehouse */
Route::get('/area-data', [InventoryController::class, 'getAreaData']);
Route::get('/warehouse-data-filter', [InventoryController::class, 'warehouseFilterData']);
Route::get('/warehouse-dispatch-filter', [InventoryController::class, 'warehouseAreaDispatch']);
Route::get('/warehouse-data', [InventoryController::class, 'getWarehouseDataCombined'])->name('warehouse.getWarehouseData');
Route::get('/warehouse-temperature', [InventoryController::class, 'getWarehouseDataWithTemperature']);




Route::group(['middleware' => ['role:super-admin|admin']], function () {

    Route::resource('permissions', PermissionController::class);
    Route::get('permissions/{permissionId}/delete', [PermissionController::class, 'destroy']);

    Route::resource('roles', RoleController::class);
    Route::get('roles/{roleId}/delete', [RoleController::class, 'destroy']);
    Route::get('roles/{roleId}/give-permissions', [RoleController::class, 'addPermissionToRole']);
    Route::put('roles/{roleId}/give-permissions', [RoleController::class, 'givePermissionToRole']);

    Route::resource('users', UserController::class);
    Route::delete('users/{userId}/delete', [UserController::class, 'destroy']);

    Route::get('departments', [DepartmentController::class, 'index'])->name('department.index');
    Route::post('departments', [DepartmentController::class, 'store'])->name('department.store');
    Route::delete('departments/{department:department_slug}/delete', [DepartmentController::class, 'destroy'])->name('department.destroy');
    Route::put('departments/{department:department_slug}/update', [DepartmentController::class, 'update'])->name('department.update');

    Route::get('positions', [PositionController::class, 'index'])->name('position.index');
    Route::delete('positions/{position:position_slug}/delete', [PositionController::class, 'destroy'])->name('positions.destroy');
    Route::put('positions/{position:position_slug}/update', [PositionController::class, 'update'])->name('positions.update');
    Route::post('positions', [PositionController::class, 'store'])->name('position.store');

    Route::get('levels', [LevelController::class, 'index'])->name('level.index');
    Route::put('levels/{level:level_slug}/update', [LevelController::class, 'update'])->name('level.update');
    Route::post('levels', [LevelController::class, 'store'])->name('level.store');
    Route::delete('levels/{level:level_slug}/delete', [LevelController::class, 'destroy'])->name('level.destroy');
});

require __DIR__ . '/auth.php';
