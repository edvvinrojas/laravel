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
use App\Http\Controllers\CategoriaEquipoController;
use App\Http\Controllers\ModeloEquipoController;
use App\Http\Controllers\CatalogoConsumibleController;

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
    Route::get('clients/{client}/branches', [BranchController::class, 'index'])->name('branches.index');
    Route::post('clients/{client}/branches', [BranchController::class, 'store'])->name('branches.store');
    Route::delete('branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');

    // Catálogos de equipos
    Route::resource('brands', BrandController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('categorias-equipo', CategoriaEquipoController::class);
    Route::resource('modelos-equipo', ModeloEquipoController::class);
    Route::resource('catalogo-consumibles', CatalogoConsumibleController::class);

    // Almacén unificado (equipos + inventario)
    Route::get('almacen', [AlmacenController::class, 'index'])->name('almacen.index');

    // Equipos
    Route::resource('equipment', EquipmentController::class);

    // Catálogos de inventario
    Route::resource('shelves', ShelfController::class);
    Route::resource('item-catalog', ItemCatalogController::class);

    // Inventario
    Route::resource('inventory', InventoryController::class);

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
    Route::resource('absences', AbsenceController::class);
    Route::resource('administrative-records', AdministrativeRecordController::class);

    // Usuarios (solo admin)
    Route::resource('users', UserController::class)->middleware('role:administrador');

    // Producción (planes mensuales)
    Route::resource('production', MonthlyPlanController::class);

    // Tipos de servicio (catálogo para producción)
    Route::resource('service-types', ServiceTypeController::class);

    // Auditoría (solo admin)
    Route::get('audit', [AuditController::class, 'index'])->name('audit.index')->middleware('role:administrador');

    // Perfil propio
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    // Notificaciones
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
});
