# PHASE CR-1 — Commercial Sales Reports

## Implementation Report

**Module:** Commercial → Reports → Sales Reports  
**Route:** `commercial.reports.sales.index`  
**Audience:** Sales Team, Commercial Manager, Branch Sales Manager, Customer Service  
**Scope:** Commercial department reporting only — not Executive Intelligence, Financial Intelligence, or Executive 360.

---

## Readiness Table (Required First)

Displayed at the top of the Sales Reports workspace before KPIs and tabs.

| Source | Table | Required Columns | Status Check |
|--------|-------|------------------|--------------|
| Customers | `customers` | `company_id`, `branch_id`, `status` | `Schema::hasTable` + column check |
| Quotations | `quotations` | `company_id`, `branch_id`, `customer_id`, `prepared_by`, `status`, `quotation_date`, `valid_until`, `total_amount` | Same |
| Sales Orders | `sales_orders` | `company_id`, `branch_id`, `customer_id`, `created_by`, `status`, `order_date`, `total_amount` | Same |
| Branches | `branches` | `company_id`, `name`, `is_active` | Same |
| Users / Salespersons | `users` | `company_id`, `name` | Same |
| Leads (optional) | `leads` | `company_id`, `branch_id`, `status` | Optional — lost opportunities tab |

**Service:** `App\Support\Commercial\Reports\CommercialSalesReportReadiness`  
**View:** `resources/views/admin/commercial/reports/sales/partials/readiness-table.blade.php`

KPIs show `—` with hint when core sources are not ready. Tabs remain accessible with placeholder states.

---

## Routes

| Method | URI | Name | Permission |
|--------|-----|------|------------|
| GET | `/admin/commercial/reports/sales` | `commercial.reports.sales.index` | `commercial.reports.sales.view` |
| POST | `/admin/commercial/reports/sales/export` | `commercial.reports.sales.export` | `commercial.reports.sales.export` |

**File:** `routes/admin_commercial.php`  
**Middleware:** `auth`, `verified`, `tenant`, `CaptureWorkspaceNavigationQuery`

---

## Controllers

| Class | Responsibility |
|-------|----------------|
| `App\Http\Controllers\Admin\Commercial\CommercialSalesReportController` | Index presentation, queued export dispatch |

---

## Services

| Class | Responsibility |
|-------|----------------|
| `CommercialSalesReportScope` | Immutable filter/scope DTO |
| `CommercialSalesReportScopeResolver` | Resolves tenant scope, filter lists, permissions, readiness |
| `CommercialSalesReportReadiness` | Data-source readiness assessment |
| `CommercialSalesReportQueries` | Aggregate SQL queries — no collection reporting |
| `CommercialSalesReportPresenter` | KPI cache, tab payload assembly |

---

## Queries (Operational Truth Only)

Reads from existing tables only:

- **Revenue orders:** `sales_orders` excluding `draft` and `cancelled`
- **Salesperson:** `sales_orders.created_by` (proxy for salesperson)
- **Quotation conversion:** `quotations.prepared_by` + `status = converted`
- **Lost orders:** cancelled `sales_orders`, expired `quotations`, lost `leads`
- **Lifetime value:** `SUM(total_amount)` per `customer_id` (all-time, branch-scoped)

**No duplicate or reporting-only tables created.**

### Tabs Implemented

1. Sales Summary — metrics + branch/salesperson breakdown  
2. Sales By Day — paginated  
3. Sales By Week — paginated  
4. Sales By Month — paginated  
5. Sales By Customer — paginated  
6. Sales By Branch — paginated  
7. Sales By Salesperson — paginated + conversion %  
8. Top Customers — Top 10/25/50 by revenue, orders, or lifetime  
9. Lost Orders — cancelled, expired, lost opportunities, reason analysis  
10. Sales Trends — daily/weekly/monthly/quarterly/yearly bar charts  

---

## Permissions

| Permission | Purpose |
|------------|---------|
| `commercial.reports.sales.view` | Access workspace |
| `commercial.reports.sales.export` | Queue PDF/Excel/CSV exports |
| `commercial.reports.sales.manage` | Reserved for future report configuration |

**Seeder:** `database/seeders/RolesAndPermissionsSeeder.php`  
**Catalog:** `config/permission_catalog.php` → `commercial.sales_reports`

### Role Assignments

| Role | view | export | manage |
|------|------|--------|--------|
| Company Admin | ✓ | ✓ | ✓ |
| Branch Manager | ✓ | ✓ | — |
| Sales | ✓ | — | — |

---

## Indexes

**Migration:** `2026_06_19_100000_add_sales_orders_reporting_indexes.php`

### `sales_orders`

- `sales_orders_reporting_scope_idx` — `(company_id, branch_id, order_date)`
- `sales_orders_reporting_status_idx` — `(company_id, status, order_date)`
- `sales_orders_reporting_customer_idx` — `(company_id, customer_id, order_date)`
- `sales_orders_reporting_salesperson_idx` — `(company_id, created_by, order_date)`

### `quotations`

- `quotations_reporting_scope_idx` — `(company_id, branch_id, quotation_date)`
- `quotations_reporting_salesperson_idx` — `(company_id, prepared_by, quotation_date)`
- `quotations_reporting_lost_idx` — `(company_id, status, valid_until)`

---

## Performance Controls

| Control | Implementation |
|---------|----------------|
| Pagination | 25 rows/page on tab tables via `LengthAwarePaginator` |
| Aggregate queries | `GROUP BY`, `SUM`, `COUNT`, `DISTINCT` in SQL |
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
| CSV | `ExportCommercialSalesReportJob` | `storage/app/exports/commercial/sales/{company}/` |
| Excel | Same job | UTF-8 BOM TSV (`.xls`) |
| PDF | Same job | Printable HTML (`.html`) |

**Queue:** `exports` (via `PlatformJob::useQueue('exports')`)  
**No synchronous exports.**

---

## Filter Persistence

**GET query parameters:** `from_date`, `to_date`, `branch_id`, `customer_id`, `salesperson_id`, `status`, `search`, `tab`, `top_limit`, `top_by`, `page`

**Session preservation:** `CaptureWorkspaceNavigationQuery` + `config/workspace_navigation.php`

---

## Navigation

**Workspace card:** `config/commercial_workspaces.php` → Reports → Sales Reports (live link)

---

## Tests Added

**File:** `tests/Feature/Commercial/CommercialSalesReportTest.php`

| Test | Coverage |
|------|----------|
| `test_sales_reports_requires_permission` | 403 without permission |
| `test_sales_reports_index_loads` | Page, readiness, KPIs, tabs |
| `test_sales_reports_show_kpis_for_orders` | Live KPI from factory order |
| `test_filters_persist_in_query_string` | Filter persistence |
| `test_export_requires_permission` | Export gated |
| `test_export_queues_job` | `Bus::fake` — job dispatched |

**Updated:** `tests/Feature/Commercial/CommercialWorkspaceTest.php` — Sales Reports linked, other tiles remain coming soon.

---

## Files Created / Modified

### Created

- `app/Support/Commercial/Reports/*` (5 classes)
- `app/Http/Controllers/Admin/Commercial/CommercialSalesReportController.php`
- `app/Jobs/Commercial/ExportCommercialSalesReportJob.php`
- `resources/views/admin/commercial/reports/sales/**`
- `database/migrations/2026_06_19_100000_add_sales_orders_reporting_indexes.php`
- `tests/Feature/Commercial/CommercialSalesReportTest.php`
- `docs/PHASE_CR1_COMMERCIAL_SALES_REPORTS.md`

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
php artisan test --filter=CommercialSalesReportTest
```

**Result:** 6/6 tests passed.
