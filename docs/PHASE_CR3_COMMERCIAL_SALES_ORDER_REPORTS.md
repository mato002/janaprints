# PHASE CR-3 — Commercial Sales Order Reports

## Implementation Report

**Module:** Commercial → Reports → Sales Order Reports  
**Route:** `admin.commercial.reports.sales_orders.index`  
**Audience:** Sales Team, Commercial Manager, Branch Sales Manager, Customer Service  
**Scope:** Commercial department order reporting only — not Executive Intelligence, Production module, or duplicate sales order business logic.

---

## Readiness Table (Required First)

Displayed at the top of the Sales Order Reports workspace before KPIs and tabs.

| Source | Table | Required Columns | Status Check |
|--------|-------|------------------|--------------|
| Sales Orders | `sales_orders` | `company_id`, `branch_id`, `customer_id`, `quotation_id`, `created_by`, `status`, `order_date`, `required_date`, `total_amount`, `order_number` | `Schema::hasTable` + column check |
| Sales Order Items | `sales_order_items` | `sales_order_id`, `item_name`, `quantity`, `unit_price`, `line_total` | Same |
| Customers | `customers` | `company_id`, `branch_id`, `company_name` | Same |
| Quotations | `quotations` | `company_id`, `branch_id`, `customer_id`, `status`, `quotation_date`, `quotation_number`, `total_amount` | Same |
| Branches | `branches` | `company_id`, `name`, `is_active` | Same |
| Users / Salespersons | `users` | `company_id`, `name` | Same |

**Service:** `App\Support\Commercial\Reports\CommercialSalesOrderReportReadiness`  
**View:** `resources/views/admin/commercial/reports/sales-orders/partials/readiness-table.blade.php`

KPIs show `—` with hint when core sources are not ready. Tabs remain accessible with placeholder states.

---

## Routes

| Method | URI | Name | Permission |
|--------|-----|------|------------|
| GET | `/admin/commercial/reports/sales-orders` | `admin.commercial.reports.sales_orders.index` | `admin.commercial.reports.sales_orders.view` |
| POST | `/admin/commercial/reports/sales-orders/export` | `admin.commercial.reports.sales_orders.export` | `admin.commercial.reports.sales_orders.export` |

**File:** `routes/admin_commercial.php`  
**Middleware:** `auth`, `verified`, `tenant`, `CaptureWorkspaceNavigationQuery`

---

## Controllers

| Class | Responsibility |
|-------|----------------|
| `App\Http\Controllers\Admin\Commercial\CommercialSalesOrderReportController` | Index presentation, queued export dispatch |

---

## Services

| Class | Responsibility |
|-------|----------------|
| `CommercialSalesOrderReportScope` | Immutable filter/scope DTO |
| `CommercialSalesOrderReportScopeResolver` | Resolves tenant scope, filter lists, permissions, readiness |
| `CommercialSalesOrderReportReadiness` | Data-source readiness assessment |
| `CommercialSalesOrderReportQueries` | Aggregate SQL queries — no collection reporting |
| `CommercialSalesOrderReportPresenter` | KPI cache, tab payload assembly |

---

## Queries (Operational Truth Only)

Reads from existing tables only:

- **All orders:** `sales_orders` scoped by company, branch, date, filters
- **Open orders:** `confirmed`, `ready_for_production`, `in_production`, `on_hold`
- **Pending orders:** `draft`, `confirmed`, `on_hold`
- **Completed orders:** `completed`, `delivered`, `closed`
- **Salesperson:** `sales_orders.created_by`
- **Quote conversion:** `quotations` with `status = converted` vs total quotations in period
- **Completion rate:** completed orders / non-draft orders in period

**No duplicate or reporting-only tables created.**

### Tabs Implemented

1. Sales Order Summary — metrics + status breakdown  
2. Open Orders — paginated order list  
3. Pending Orders — paginated  
4. Completed Orders — paginated  
5. Cancelled Orders — paginated  
6. Orders By Customer — paginated aggregate  
7. Orders By Branch — paginated aggregate  
8. Orders By Salesperson — paginated aggregate  
9. Order Aging — open-order age buckets (0–7, 8–14, 15–30, 31–60, 60+ days)  
10. Order Value Analysis — value distribution buckets  
11. Orders Awaiting Production — `ready_for_production` list  
12. Orders Converted From Quotations — orders joined to `quotations`  

---

## KPIs

| KPI | Source |
|-----|--------|
| Total Orders | `COUNT(*)` on scoped orders |
| Open Orders | Open status set |
| Completed Orders | Completed status set |
| Cancelled Orders | `status = cancelled` |
| Total Order Value | `SUM(total_amount)` |
| Average Order Value | Total value / total orders |
| Orders Awaiting Production | `status = ready_for_production` |
| Quote-to-Order Conversion | Converted quotations / total quotations |
| Order Completion Rate | Completed / non-draft orders |

---

## Permissions

| Permission | Purpose |
|------------|---------|
| `admin.commercial.reports.sales_orders.view` | Access workspace |
| `admin.commercial.reports.sales_orders.export` | Queue PDF/Excel/CSV exports |

**Seeder:** `database/seeders/RolesAndPermissionsSeeder.php`  
**Catalog:** `config/permission_catalog.php` → `commercial.sales_order_reports`

### Role Assignments

| Role | view | export |
|------|------|--------|
| Company Admin | ✓ | ✓ |
| Branch Manager | ✓ | ✓ |
| Sales | ✓ | — |

---

## Indexes

**Existing (CR-1):** `2026_06_19_100000_add_sales_orders_reporting_indexes.php`

- `sales_orders_reporting_scope_idx` — `(company_id, branch_id, order_date)`
- `sales_orders_reporting_status_idx` — `(company_id, status, order_date)`
- `sales_orders_reporting_customer_idx` — `(company_id, customer_id, order_date)`
- `sales_orders_reporting_salesperson_idx` — `(company_id, created_by, order_date)`

**Added (CR-3):** `2026_06_20_100000_add_sales_orders_quotation_reporting_index.php`

- `sales_orders_reporting_quotation_idx` — `(company_id, quotation_id, order_date)`

---

## Performance Controls

| Control | Implementation |
|---------|----------------|
| Pagination | 25 rows/page on tab tables via `LengthAwarePaginator` |
| Aggregate queries | `GROUP BY`, `SUM`, `COUNT`, `CASE` buckets in SQL |
| No N+1 | Branch/customer/user names loaded via `whereIn` pluck maps |
| No full load | No `SalesOrder::all()` or collection aggregation |
| Date filtering | `whereDate(order_date, >=, <=)` on all scoped queries |
| Branch filtering | `branch_id` on scope when set |
| KPI cache | `PlatformCacheService` — 60s TTL, key includes filter hash |
| Blade | Presentation only — no heavy calculations in views |

---

## Exports (Queued Only)

| Format | Job | Output |
|--------|-----|--------|
| CSV | `ExportCommercialSalesOrderReportJob` | `storage/app/exports/commercial/sales-orders/{company}/` |
| Excel | Same job | UTF-8 BOM TSV (`.xls`) |
| PDF | Same job | Printable HTML (`.html`) |

**Queue:** `exports` (via `PlatformJob::useQueue('exports')`)  
**No synchronous exports.**

---

## Filter Persistence

**GET query parameters:** `from_date`, `to_date`, `branch_id`, `customer_id`, `salesperson_id`, `status`, `quotation_source`, `search`, `tab`, `page`

**Session preservation:** `CaptureWorkspaceNavigationQuery` + `config/workspace_navigation.php`

---

## Navigation

**Workspace card:** `config/commercial_workspaces.php` → Reports → Sales Order Reports (live link)

---

## Tests Added

**File:** `tests/Feature/Commercial/CommercialSalesOrderReportTest.php`

| Test | Coverage |
|------|----------|
| `test_sales_order_reports_requires_permission` | 403 without permission |
| `test_sales_order_reports_index_loads` | Page, readiness, KPIs, tabs |
| `test_sales_order_reports_show_kpis_for_orders` | Live KPI from factory order |
| `test_filters_persist_in_query_string` | Filter persistence |
| `test_export_requires_permission` | Export gated |
| `test_export_queues_job` | `Bus::fake` — job dispatched |

**Updated:** `tests/Feature/Commercial/CommercialWorkspaceTest.php` — Sales Order Reports linked.

---

## Files Created / Modified

### Created

- `app/Support/Commercial/Reports/CommercialSalesOrderReport*.php` (5 classes)
- `app/Http/Controllers/Admin/Commercial/CommercialSalesOrderReportController.php`
- `app/Jobs/Commercial/ExportCommercialSalesOrderReportJob.php`
- `resources/views/admin/commercial/reports/sales-orders/**`
- `database/migrations/2026_06_20_100000_add_sales_orders_quotation_reporting_index.php`
- `tests/Feature/Commercial/CommercialSalesOrderReportTest.php`
- `docs/PHASE_CR3_COMMERCIAL_SALES_ORDER_REPORTS.md`

### Modified

- `routes/admin_commercial.php`
- `config/commercial_workspaces.php`
- `config/workspace_navigation.php`
- `config/permission_catalog.php`
- `database/seeders/RolesAndPermissionsSeeder.php`
- `tests/Feature/Commercial/CommercialWorkspaceTest.php`

---

## Verification

```bash
php artisan migrate
php artisan test --filter=CommercialSalesOrderReportTest
```
