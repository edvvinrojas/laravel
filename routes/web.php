<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\RentController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\PrintCounterController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SparepartController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\RepairController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\VacationController;
use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\AdministrativeRecordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ShelfController;
use App\Http\Controllers\ItemCatalogController;
use App\Http\Controllers\MonthlyPlanController;
use App\Http\Controllers\ServiceTypeController;
use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\RhController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceOrderController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\AccesorioController;
use App\Http\Controllers\ConsumibleController;
use App\Http\Controllers\TiEquipmentController;

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected routes
Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Clientes
    Route::resource('clients', ClientController::class);
    Route::delete('clients/{client}/contacts/{contact}', [ClientController::class, 'destroyContact'])->name('clients.contacts.destroy');
    Route::post('clients/{client}/documents', [ClientController::class, 'uploadDocument'])->name('clients.documents.upload');
    Route::delete('clients/{client}/documents/{docType}', [ClientController::class, 'destroyDocument'])->name('clients.documents.destroy');
    Route::get('clients/{client}/branches', [BranchController::class, 'index'])->name('branches.index');
    Route::post('clients/{client}/branches', [BranchController::class, 'store'])->name('branches.store');
    Route::delete('branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');

    // Catálogos
    Route::resource('brands', BrandController::class);
    Route::resource('suppliers', SupplierController::class);

    // Productos / Accesorios / Consumibles (dentro de Almacén)
    Route::resource('productos', ProductoController::class);
    Route::resource('accesorios', AccesorioController::class)->except(['index','show']);
    Route::resource('consumibles', ConsumibleController::class)->except(['index','show']);

    // Almacén unificado (equipos + inventario)
    Route::get('almacen', [AlmacenController::class, 'index'])->name('almacen.index');

    // Equipos
    Route::resource('equipment', EquipmentController::class);
    Route::get('equipment/{equipment}/producto-detalle', [EquipmentController::class, 'productoDetalle'])->name('equipment.producto-detalle');

    // Catálogos de inventario
    Route::resource('shelves', ShelfController::class);
    Route::resource('item-catalog', ItemCatalogController::class);

    // Inventario
    Route::resource('inventory', InventoryController::class);

    // API JSON helpers (para selects dinámicos)
    Route::get('api/clients/{client}/branches', fn(\App\Models\Client $client) =>
        $client->branches()->select('id','name','address','city','colonia','zip_code','latitude','longitude')->orderBy('name')->get()
    )->name('api.client.branches');
    Route::get('api/branches/{branch}/areas', fn(\App\Models\Branch $branch) =>
        $branch->areas()->select('id','name')->orderBy('name')->get()
    )->name('api.branch.areas');
    Route::get('api/areas/{area}/items', function(\App\Models\Area $area) {
        $items = \App\Models\Item::whereHas('rents', fn($q) =>
            $q->where('area_id', $area->id)->where('contract_status', 'VIGENTE')
        )->with('brand')->get()->map(fn($i) => [
            'id'    => $i->id,
            'model' => $i->model,
            'serie' => $i->serie,
            'sku'   => $i->sku,
            'brand' => $i->brand->name ?? '',
        ]);
        return response()->json($items);
    })->name('api.area.items');
    Route::get('api/rents/{rent}/billing-amount', function(\App\Models\Rent $rent) {
        $lastCounter = $rent->printCounters()->where('is_active', true)->orderByDesc('id')->first();
        $excess = $lastCounter ? (float) $lastCounter->total_excess_amount : 0;
        return response()->json([
            'base'    => (float) $rent->rent,
            'excess'  => $excess,
            'total'   => (float) $rent->rent + $excess,
            'client_id' => $rent->client_id,
        ]);
    })->name('api.rent.billing-amount');
    Route::get('api/sales/{sale}/billing-amount', function(\App\Models\Sale $sale) {
        return response()->json([
            'total'     => (float) $sale->sale_price,
            'client_id' => $sale->client_id,
        ]);
    })->name('api.sale.billing-amount');

    // Rentas
    Route::resource('rents', RentController::class);

    // Ventas
    Route::resource('sales', SaleController::class);

    // Facturación / Cobranza
    Route::resource('billing', BillingController::class);
    Route::patch('billing/{billing}/pay', [BillingController::class, 'markPaid'])->name('billing.pay');

    // Contadores de impresión
    Route::resource('print-counters', PrintCounterController::class);

    // Compras
    Route::resource('purchases', PurchaseController::class);
    Route::patch('purchases/{purchase}/approve', [PurchaseController::class, 'approve'])->name('purchases.approve');
    Route::patch('purchases/{purchase}/status',  [PurchaseController::class, 'updateStatus'])->name('purchases.status');

    // Refacciones
    Route::resource('spareparts', SparepartController::class);

    // Tickets
    Route::resource('tickets', TicketController::class);
    Route::patch('tickets/{ticket}/close', [TicketController::class, 'close'])->name('tickets.close');

    // Taller / Reparaciones
    Route::resource('repairs', RepairController::class);

    // Rutas
    Route::resource('routes', RouteController::class);
    Route::post('routes/{route}/stops', [RouteController::class, 'storeStop'])->name('routes.stops.store');
    Route::patch('routes/{route}/stops/{stop}/complete', [RouteController::class, 'completeStop'])->name('routes.stops.complete');
    Route::delete('routes/{route}/stops/{stop}', [RouteController::class, 'destroyStop'])->name('routes.stops.destroy');

    // RH hub
    Route::get('rh', [RhController::class, 'index'])->name('rh.index');

    // RH
    Route::resource('employees', EmployeeController::class);
    Route::resource('payrolls', PayrollController::class);
    Route::resource('vacations', VacationController::class);
    Route::patch('vacations/{vacation}/approve', [VacationController::class, 'approve'])->name('vacations.approve');
    Route::patch('vacations/{vacation}/reject',  [VacationController::class, 'reject'])->name('vacations.reject');
    Route::resource('absences', AbsenceController::class);
    Route::patch('absences/{absence}/approve', [AbsenceController::class, 'approve'])->name('absences.approve');
    Route::patch('absences/{absence}/reject',  [AbsenceController::class, 'reject'])->name('absences.reject');
    Route::resource('administrative-records', AdministrativeRecordController::class);

    // Usuarios (admin o departamento TI)
    Route::resource('users', UserController::class)->middleware('role:administrador,dept:ti');

    // Órdenes de Servicio
    Route::resource('service-orders', ServiceOrderController::class);

    // Producción (planes mensuales)
    Route::resource('production', MonthlyPlanController::class);

    // Tipos de servicio (catálogo para producción)
    Route::resource('service-types', ServiceTypeController::class);

    // TI – Inventario interno
    Route::resource('ti-equipment', TiEquipmentController::class);
    Route::post('ti-equipment/{tiEquipment}/peripherals',           [TiEquipmentController::class, 'storePeripheral'])->name('ti-equipment.peripherals.store');
    Route::delete('ti-equipment/{tiEquipment}/peripherals/{peripheral}', [TiEquipmentController::class, 'destroyPeripheral'])->name('ti-equipment.peripherals.destroy');
    Route::get('ti-licenses',                                        [TiEquipmentController::class, 'licensesIndex'])->name('ti-equipment.licenses');
    Route::post('ti-licenses',                                       [TiEquipmentController::class, 'licenseStore'])->name('ti-equipment.licenses.store');
    Route::delete('ti-licenses/{license}',                          [TiEquipmentController::class, 'licenseDestroy'])->name('ti-equipment.licenses.destroy');

    // Auditoría (solo admin)
    Route::get('audit', [AuditController::class, 'index'])->name('audit.index')->middleware('role:administrador');

    // Perfil propio
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('profile/avatar', [ProfileController::class, 'uploadAvatar'])->name('profile.avatar');
    Route::delete('profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');

    // Notificaciones
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
});
