<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->string('facturacom_uid', 30)->nullable()->after('invoice_number')->index();
            $table->string('facturacom_uuid', 50)->nullable()->after('facturacom_uid')->index();
            $table->string('facturacom_folio', 50)->nullable()->after('facturacom_uuid')->index();
            $table->string('facturacom_status', 50)->nullable()->after('facturacom_folio');
            $table->timestamp('facturacom_synced_at')->nullable()->after('facturacom_status');
            $table->json('facturacom_last_response')->nullable()->after('facturacom_synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->dropColumn([
                'facturacom_uid',
                'facturacom_uuid',
                'facturacom_folio',
                'facturacom_status',
                'facturacom_synced_at',
                'facturacom_last_response',
            ]);
        });
    }
};
