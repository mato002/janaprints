<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inventory_items')) {
            return;
        }

        if (! Schema::hasColumn('inventory_items', 'press_process')) {
            Schema::table('inventory_items', function (Blueprint $table) {
                $table->string('press_process', 20)->nullable()->after('stock_role');
                $table->index(['company_id', 'branch_id', 'press_process'], 'inventory_items_press_process_idx');
            });
        }

        $this->backfill();
    }

    public function down(): void
    {
        if (! Schema::hasTable('inventory_items') || ! Schema::hasColumn('inventory_items', 'press_process')) {
            return;
        }

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropIndex('inventory_items_press_process_idx');
            $table->dropColumn('press_process');
        });
    }

    protected function backfill(): void
    {
        $digital = [
            'PAP-ADEST', 'PAP-ART-130', 'PAP-ART-150', 'PAP-ART-170', 'PAP-ART-200',
            'PAP-ART-250', 'PAP-ART-300', 'PAP-ART-350', 'PAP-PHOTO-A3', 'PAP-PHOTO-A4',
            'PAP-SPECIAL',
        ];
        $shared = [
            'PAP-BOND80-A3', 'PAP-BOND80-A4',
            'ENV-A4-W', 'ENV-A4-BR', 'ENV-DL-W', 'ENV-A5-W', 'ENV-A5-BR',
        ];
        $offset = [
            'PAP-NCR-WHITE', 'PAP-NCR-BLUE', 'PAP-NCR-PINK', 'PAP-NCR-GREEN', 'PAP-NCR-YELLOW',
            'PAP-BANK-WHITE', 'PAP-BANK-BLUE', 'PAP-BANK-YELLOW', 'PAP-BANK-PINK', 'PAP-BANK-GREEN',
            'PAP-MAN-PINK-160', 'PAP-MAN-PINK-224', 'PAP-MAN-PINK-300',
            'PAP-MAN-YEL-160', 'PAP-MAN-YEL-224', 'PAP-MAN-YEL-300',
            'PAP-MAN-GRN-160', 'PAP-MAN-GRN-224', 'PAP-MAN-GRN-300',
            'PAP-MAN-WHT-160', 'PAP-MAN-WHT-224', 'PAP-MAN-WHT-300',
            'PAP-PINDO',
        ];

        DB::table('inventory_items')->whereIn('sku', $digital)->update(['press_process' => 'digital']);
        DB::table('inventory_items')->whereIn('sku', $shared)->update(['press_process' => 'shared']);
        DB::table('inventory_items')->whereIn('sku', $offset)->update(['press_process' => 'offset']);

        DB::table('inventory_items')
            ->whereNull('press_process')
            ->where('description', 'Digital press material')
            ->update(['press_process' => 'digital']);

        DB::table('inventory_items')
            ->whereNull('press_process')
            ->where('description', 'Offset press material')
            ->update(['press_process' => 'offset']);
    }
};
