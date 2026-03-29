<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar client_id a contacts
        Schema::table('contacts', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('id')->constrained('clients')->nullOnDelete();
        });

        // 2. Quitar contact_id de clients
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['contact_id']);
            $table->dropColumn('contact_id');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
        });
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
        });
    }
};
