<?php

namespace App\Support\Commercial\Reports\Exports;

use App\Support\Commercial\Reports\Exports\Contracts\CommercialReportExporter;
use App\Support\Commercial\Reports\Exports\Exporters\ArtworkReportExporter;
use App\Support\Commercial\Reports\Exports\Exporters\ConversionReportExporter;
use App\Support\Commercial\Reports\Exports\Exporters\CustomerReportExporter;
use App\Support\Commercial\Reports\Exports\Exporters\QuotationReportExporter;
use App\Support\Commercial\Reports\Exports\Exporters\SalesOrderReportExporter;
use App\Support\Commercial\Reports\Exports\Exporters\PosReportExporter;
use App\Support\Commercial\Reports\Exports\Exporters\SalesReportExporter;
use App\Support\Inventory\Reports\Exports\InventoryReportExporter;
use App\Support\Procurement\Performance\Exports\SupplierPerformanceExporter;
use App\Support\Procurement\Reports\Exports\ProcurementReportExporter;
use App\Support\Production\Reports\Exports\CostingReportExporter;
use InvalidArgumentException;

class CommercialReportExportRegistry
{
    /** @var array<string, class-string<CommercialReportExporter>> */
    protected static array $map = [
        'sales' => SalesReportExporter::class,
        'quotations' => QuotationReportExporter::class,
        'sales_orders' => SalesOrderReportExporter::class,
        'customers' => CustomerReportExporter::class,
        'artwork' => ArtworkReportExporter::class,
        'conversion' => ConversionReportExporter::class,
        'pos' => PosReportExporter::class,
        'inventory' => InventoryReportExporter::class,
        'procurement' => ProcurementReportExporter::class,
        'supplier_performance' => SupplierPerformanceExporter::class,
        'costing' => CostingReportExporter::class,
    ];

    /**
     * @return list<string>
     */
    public static function modules(): array
    {
        return array_keys(self::$map);
    }

    public static function resolve(string $module): CommercialReportExporter
    {
        $class = self::$map[$module] ?? null;

        if ($class === null) {
            throw new InvalidArgumentException("No commercial report exporter registered for module [{$module}].");
        }

        return app($class);
    }
}
