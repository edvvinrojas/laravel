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
use App\Http\Controllers\TiEquipmentController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\ClientPortalController;
use App\Http\Controllers\ItRequestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SkuController;

// Portal público de cliente (sin autenticación)
Route::get('/portal/contadores/{token}', [ClientPortalController::class, 'show'])->name('portal.counters');

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
    Route::post('clients/{client}/portal-token',   [ClientPortalController::class, 'generateToken'])->name('clients.portal.generate');
    Route::delete('clients/{client}/portal-token', [ClientPortalController::class, 'revokeToken'])->name('clients.portal.revoke');
    Route::post('branches/{branch}/areas', [AreaController::class, 'store'])->name('branches.areas.store');
    Route::delete('branches/{branch}/areas/{area}', [AreaController::class, 'destroy'])->name('branches.areas.destroy');

    // Catálogos
    Route::resource('brands', BrandController::class);
    Route::resource('suppliers', SupplierController::class);

    // Almacén unificado (equipos + inventario)
    Route::get('almacen', [AlmacenController::class, 'index'])->name('almacen.index');

    // Equipos
    Route::resource('equipment', EquipmentController::class);

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
    Route::get('api/branches/{branch}/service-locations', function(\App\Models\Branch $branch) {
        $rentLocations = \App\Models\Rent::query()
            ->where('branch_id', $branch->id)
            ->whereNotNull('area_id')
            ->whereNotNull('item_id')
            ->with(['area:id,name', 'item.brand:id,name'])
            ->get()
            ->map(function ($rent) {
                return [
                    'area_id'   => $rent->area_id,
                    'item_id'   => $rent->item_id,
                    'area_name' => $rent->area?->name,
                    'model'     => $rent->item?->model,
                    'serie'     => $rent->item?->serie,
                    'sku'       => $rent->item?->sku,
                    'brand'     => $rent->item?->brand?->name ?? '',
                ];
            });

        $saleLocations = \App\Models\Sale::query()
            ->where('branch_id', $branch->id)
            ->whereNotNull('area_id')
            ->whereNotNull('item_id')
            ->with(['area:id,name', 'item.brand:id,name'])
            ->get()
            ->map(function ($sale) {
                return [
                    'area_id'   => $sale->area_id,
                    'item_id'   => $sale->item_id,
                    'area_name' => $sale->area?->name,
                    'model'     => $sale->item?->model,
                    'serie'     => $sale->item?->serie,
                    'sku'       => $sale->item?->sku,
                    'brand'     => $sale->item?->brand?->name ?? '',
                ];
            });

        $locations = $rentLocations
            ->concat($saleLocations)
            ->filter(fn($row) => $row['area_id'] && $row['item_id'])
            ->unique(fn($row) => $row['area_id'].'-'.$row['item_id'])
            ->sortBy(['area_name', 'brand', 'model'])
            ->values();

        return response()->json($locations);
    })->name('api.branch.service-locations');
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
        $unbilled = $rent->printCounters()
            ->where('is_active', true)
            ->where('is_billed', false)
            ->get();
        // Calcular exceso desde los datos crudos + condiciones del contrato
        $excess = $unbilled->sum(function($pc) use ($rent) {
            $bnEx    = max(0, ($pc->bn_current - $pc->bn_previous) - $rent->bn_included);
            $colorEx = max(0, ($pc->color_current - $pc->color_previous) - $rent->color_included);
            return ($bnEx * $rent->bn_cost_per_excess) + ($colorEx * $rent->color_cost_per_excess);
        });
        $nContadores = $unbilled->filter(function($pc) use ($rent) {
            $bnEx    = max(0, ($pc->bn_current - $pc->bn_previous) - $rent->bn_included);
            $colorEx = max(0, ($pc->color_current - $pc->color_previous) - $rent->color_included);
            return ($bnEx + $colorEx) > 0;
        })->count();
        return response()->json([
            'base'         => (float) $rent->rent,
            'excess'       => (float) $excess,
            'total'        => (float) $rent->rent + $excess,
            'n_contadores' => $nContadores,
            'client_id'    => $rent->client_id,
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
    Route::get('rents/{rent}/pdf', [RentController::class, 'pdf'])->name('rents.pdf');

    // Ventas
    Route::resource('sales', SaleController::class);
    Route::get('sales/{sale}/pdf', [SaleController::class, 'pdf'])->name('sales.pdf');

    // Facturación / Cobranza
    Route::resource('billing', BillingController::class);
    Route::get('billing/{billing}/pdf', [BillingController::class, 'pdf'])->name('billing.pdf');
    Route::patch('billing/{billing}/pay', [BillingController::class, 'markPaid'])->name('billing.pay');
    Route::post('billing/{billing}/facturacom/stamp', [BillingController::class, 'stampFacturaCom'])->name('billing.facturacom.stamp');
    Route::post('billing/{billing}/facturacom/sync', [BillingController::class, 'syncFacturaCom'])->name('billing.facturacom.sync');

    // Reportes
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    // Búsqueda global
    Route::get('api/search', [SearchController::class, 'search'])->name('search');

    // Configuración de SKU (TI)
    Route::get('sku', [SkuController::class, 'index'])->name('sku.index');
    Route::post('sku', [SkuController::class, 'store'])->name('sku.store');
    Route::put('sku/{skuFormat}', [SkuController::class, 'update'])->name('sku.update');
    Route::post('sku/{skuFormat}/reset', [SkuController::class, 'reset'])->name('sku.reset');
    Route::delete('sku/{sku}', [SkuController::class, 'destroy'])->name('sku.destroy');

    // Contadores de impresión
    Route::resource('print-counters', PrintCounterController::class);
    Route::post('print-counters/{printCounter}/bill-excess', [PrintCounterController::class, 'billExcess'])->name('print-counters.bill-excess');

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
    Route::patch('repairs/{repair}/listo', [RepairController::class, 'markListo'])->name('repairs.listo');

    // Rutas
    Route::resource('routes', RouteController::class);
    Route::post('routes/{route}/stops',                       [RouteController::class, 'storeStop'])->name('routes.stops.store');
    Route::patch('routes/{route}/stops/{stop}/complete',      [RouteController::class, 'completeStop'])->name('routes.stops.complete');
    Route::patch('routes/{route}/stops/{stop}/postpone',      [RouteController::class, 'postponeStop'])->name('routes.stops.postpone');
    Route::delete('routes/{route}/stops/{stop}',              [RouteController::class, 'destroyStop'])->name('routes.stops.destroy');
    Route::patch('routes/{route}/complete',                   [RouteController::class, 'completeRoute'])->name('routes.complete');

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
    Route::post('ti-equipment/{tiEquipment}/licenses/attach',             [TiEquipmentController::class, 'attachLicense'])->name('ti-equipment.licenses.attach');
    Route::delete('ti-equipment/{tiEquipment}/licenses/{license}/detach',[TiEquipmentController::class, 'detachLicense'])->name('ti-equipment.licenses.detach');
    Route::get('ti-equipment/{tiEquipment}/responsiva',                  [TiEquipmentController::class, 'responsiva'])->name('ti-equipment.responsiva');
    Route::get('ti-licenses',                                        [TiEquipmentController::class, 'licensesIndex'])->name('ti-equipment.licenses');
    Route::post('ti-licenses',                                       [TiEquipmentController::class, 'licenseStore'])->name('ti-equipment.licenses.store');
    Route::delete('ti-licenses/{license}',                          [TiEquipmentController::class, 'licenseDestroy'])->name('ti-equipment.licenses.destroy');

    // TI – Mesa de Ayuda (tickets internos)
    Route::resource('it-requests', ItRequestController::class);
    Route::patch('it-requests/{itRequest}/assign', [ItRequestController::class, 'assign'])->name('it-requests.assign');

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
