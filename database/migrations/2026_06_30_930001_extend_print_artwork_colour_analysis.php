<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('print_artwork_analyses', function (Blueprint $table) {
            if (! Schema::hasColumn('print_artwork_analyses', 'colour_analysis_status')) {
                $table->string('colour_analysis_status', 30)->default('pending')->after('dominant_colours');
                $table->decimal('rgb_coverage_percent', 8, 3)->nullable()->after('colour_analysis_status');
                $table->decimal('cmyk_coverage_percent', 8, 3)->nullable()->after('rgb_coverage_percent');
                $table->decimal('cyan_coverage_percent', 8, 3)->nullable()->after('cmyk_coverage_percent');
                $table->decimal('magenta_coverage_percent', 8, 3)->nullable()->after('cyan_coverage_percent');
                $table->decimal('yellow_coverage_percent', 8, 3)->nullable()->after('magenta_coverage_percent');
                $table->decimal('black_coverage_percent', 8, 3)->nullable()->after('yellow_coverage_percent');
                $table->decimal('white_area_percent', 8, 3)->nullable()->after('black_coverage_percent');
                $table->decimal('transparent_area_percent', 8, 3)->nullable()->after('white_area_percent');
                $table->decimal('average_ink_density_percent', 8, 3)->nullable()->after('transparent_area_percent');
                $table->decimal('heavy_coverage_score', 8, 3)->nullable()->after('average_ink_density_percent');
                $table->string('coverage_class', 20)->nullable()->after('heavy_coverage_score');
                $table->json('colour_analysis_warnings')->nullable()->after('coverage_class');
                $table->json('colour_analysis_raw')->nullable()->after('colour_analysis_warnings');
                $table->timestamp('colour_analyzed_at')->nullable()->after('colour_analysis_raw');

                $table->index('colour_analysis_status', 'print_artwork_colour_status_idx');
            }
        });

        Schema::table('print_artwork_pages', function (Blueprint $table) {
            if (! Schema::hasColumn('print_artwork_pages', 'rgb_coverage_percent')) {
                $table->decimal('rgb_coverage_percent', 8, 3)->nullable()->after('colour_mode');
                $table->decimal('cmyk_coverage_percent', 8, 3)->nullable()->after('rgb_coverage_percent');
                $table->decimal('cyan_coverage_percent', 8, 3)->nullable()->after('cmyk_coverage_percent');
                $table->decimal('magenta_coverage_percent', 8, 3)->nullable()->after('cyan_coverage_percent');
                $table->decimal('yellow_coverage_percent', 8, 3)->nullable()->after('magenta_coverage_percent');
                $table->decimal('black_coverage_percent', 8, 3)->nullable()->after('yellow_coverage_percent');
                $table->decimal('white_area_percent', 8, 3)->nullable()->after('black_coverage_percent');
                $table->decimal('transparent_area_percent', 8, 3)->nullable()->after('white_area_percent');
                $table->json('dominant_colours')->nullable()->after('transparent_area_percent');
                $table->string('coverage_class', 20)->nullable()->after('dominant_colours');
                $table->json('colour_analysis_raw')->nullable()->after('coverage_class');
            }
        });
    }

    public function down(): void
    {
        Schema::table('print_artwork_analyses', function (Blueprint $table) {
            $columns = [
                'colour_analysis_status', 'rgb_coverage_percent', 'cmyk_coverage_percent',
                'cyan_coverage_percent', 'magenta_coverage_percent', 'yellow_coverage_percent',
                'black_coverage_percent', 'white_area_percent', 'transparent_area_percent',
                'average_ink_density_percent', 'heavy_coverage_score', 'coverage_class',
                'colour_analysis_warnings', 'colour_analysis_raw', 'colour_analyzed_at',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('print_artwork_analyses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('print_artwork_pages', function (Blueprint $table) {
            $columns = [
                'rgb_coverage_percent', 'cmyk_coverage_percent', 'cyan_coverage_percent',
                'magenta_coverage_percent', 'yellow_coverage_percent', 'black_coverage_percent',
                'white_area_percent', 'transparent_area_percent', 'dominant_colours',
                'coverage_class', 'colour_analysis_raw',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('print_artwork_pages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
