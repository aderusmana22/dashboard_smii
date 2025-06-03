<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QAD\InventoryController;
use App\Http\Controllers\QAD\ProductionController;
use App\Http\Controllers\QAD\SalesController;
use App\Http\Controllers\Role\PermissionController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\UserController;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardSalesController;
use App\Http\Controllers\StandardBudgetController;
use App\Http\Controllers\KanbanController;

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


    /*Dashboard Inventory*/
    Route::get('dashboard-inventory', [InventoryController::class, 'dashboardInventory'])->name('dashboard.dashboardInventory');


    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/markAllAsRead', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::delete('/notifications/clear', [NotificationController::class, 'clearAll'])->name('notifications.clear');
    Route::get('/notifications/count', function () {
        return response()->json(['count' => auth()->user()->unreadNotifications->count()]);
    })->name('notifications.count');

    // kanban
    Route::get('/kanban', [KanbanController::class, 'index'])->name('page.kanban.index');
    Route::post('/tasks', [KanbanController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}/status', [KanbanController::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::delete('/tasks/{task}', [KanbanController::class, 'destroy'])->name('tasks.destroy');
    Route::get('/tasks/approval/{token}', [KanbanController::class, 'handleApproval'])->name('tasks.handle_approval');
    Route::post('/tasks/approval/{token}', [KanbanController::class, 'handleApproval'])->name('tasks.submit_rejection');
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
