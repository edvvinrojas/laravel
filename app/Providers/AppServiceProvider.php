<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use App\Observers\AuditObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Blade::directive('activeRoute', function (string $expression) {
            return "<?php echo request()->routeIs({$expression}) ? 'active' : ''; ?>";
        });

        // Auditoría automática para modelos clave
        $models = [
            \App\Models\Client::class,
            \App\Models\Rent::class,
            \App\Models\Sale::class,
            \App\Models\Billing::class,
            \App\Models\Item::class,
            \App\Models\InventoryItem::class,
            \App\Models\Sparepart::class,
            \App\Models\Purchase::class,
            \App\Models\Ticket::class,
            \App\Models\Repair::class,
            \App\Models\Employee::class,
            \App\Models\EmployeeCredit::class,
            \App\Models\User::class,
        ];

        foreach ($models as $model) {
            $model::observe(AuditObserver::class);
        }
    }
}
