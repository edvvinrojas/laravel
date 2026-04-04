<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Vistas de compatibilidad para exponer la estructura de CopyMart
        // sin romper el esquema Laravel actual.

        DB::statement("CREATE OR REPLACE VIEW copymart_brands_v AS
            SELECT
                id AS brand_id,
                name,
                prefix
            FROM brands");

        DB::statement("CREATE OR REPLACE VIEW copymart_suppliers_v AS
            SELECT
                id AS supplier_id,
                name
            FROM suppliers");

        DB::statement("CREATE OR REPLACE VIEW copymart_items_v AS
            SELECT
                id AS item_id,
                sku,
                brand_id,
                model,
                serie,
                model_toner,
                type,
                supplier_id,
                invoice,
                cost,
                location_status,
                comments,
                created_at,
                is_active
            FROM items");

        DB::statement("CREATE OR REPLACE VIEW copymart_item_catalog_v AS
            SELECT
                id AS catalog_id,
                item_name,
                description,
                item_type,
                brand_id,
                color,
                `usage`,
                created_at,
                is_active
            FROM item_catalog");

        DB::statement("CREATE OR REPLACE VIEW copymart_shelves_v AS
            SELECT
                id AS shelf_id,
                name,
                section,
                description,
                created_at,
                is_active
            FROM shelves");

        DB::statement("CREATE OR REPLACE VIEW copymart_inventory_v AS
            SELECT
                id AS inventory_id,
                item_code,
                catalog_id,
                section,
                shelf_id,
                quality,
                entry_date,
                supplier_id,
                invoice,
                cost,
                is_available,
                comments,
                created_at,
                updated_at,
                is_active
            FROM inventory");

        DB::statement("CREATE OR REPLACE VIEW copymart_item_stock_v AS
            SELECT
                id AS stock_id,
                catalog_id,
                stock_min,
                stock_max
            FROM item_stock");

        DB::statement("CREATE OR REPLACE VIEW copymart_inventory_sequences_v AS
            SELECT
                id AS sequence_id,
                prefix,
                current_value,
                updated_at
            FROM inventory_sequences");

        DB::statement("CREATE OR REPLACE VIEW copymart_inventory_equipment_v AS
            SELECT
                inventory_id,
                item_id AS equipment_id
            FROM inventory_equipment");

        DB::statement("CREATE OR REPLACE VIEW copymart_spareparts_v AS
            SELECT
                id AS sparepart_id,
                name,
                color,
                description,
                brand,
                equipment,
                code,
                supplier,
                created_at,
                updated_at
            FROM spareparts");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS copymart_spareparts_v');
        DB::statement('DROP VIEW IF EXISTS copymart_inventory_equipment_v');
        DB::statement('DROP VIEW IF EXISTS copymart_inventory_sequences_v');
        DB::statement('DROP VIEW IF EXISTS copymart_item_stock_v');
        DB::statement('DROP VIEW IF EXISTS copymart_inventory_v');
        DB::statement('DROP VIEW IF EXISTS copymart_shelves_v');
        DB::statement('DROP VIEW IF EXISTS copymart_item_catalog_v');
        DB::statement('DROP VIEW IF EXISTS copymart_items_v');
        DB::statement('DROP VIEW IF EXISTS copymart_suppliers_v');
        DB::statement('DROP VIEW IF EXISTS copymart_brands_v');
    }
};
