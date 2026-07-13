# PHASE CR-4 — Commercial Customer Reports

## Implementation Report

**Module:** Commercial → Reports → Customer Reports  
**Route:** `admin.commercial.reports.customers.index`  
**Audience:** Sales Team, Commercial Manager, Branch Sales Manager, Customer Service  
**Scope:** Commercial department customer reporting only — not Customer 360, Executive Intelligence, or duplicate CRM workspaces.

---

## Readiness Table (Required First)

Displayed at the top of the Customer Reports workspace before KPIs and tabs.

| Source | Table | Required Columns | Status Check |
|--------|-------|------------------|--------------|
| Customers | `customers` | `company_id`, `branch_id`, `customer_type`, `status`, `created_at` | `Schema::hasTable` + column check |
| Sales Orders | `sales_orders` | `company_id`, `branch_id`, `customer_id`, `created_by`, `status`, `order_date`, `total_amount` | Same |
| Quotations | `quotations` | `company_id`, `branch_id`, `customer_id`, `prepared_by`, `status`, `quotation_date`, `total_amount` | Same |
| Branches | `branches` | `company_id`, `name`, `is_active` | Same |
| Users / Salespersons | `users` | `company_id`, `name` | Same |
| Customer Activities (optional) | `customer_activities` | `company_id`, `branch_id`, `customer_id`, `activity_type`, `activity_at` | Optional — activity tab |
| Customer Invoices (optional) | `customer_invoices` | `company_id`, `customer_id`, `invoice_date`, `total_amount`, `status` | Optional — future revenue enrichment |
| Leads (optional) | `leads` | `company_id`, `branch_id`, `status` | Optional |

**Service:** `App\Support\Commercial\Reports\CommercialCustomerReportReadiness`  
**View:** Reuses `resources/views/admin/commercial/reports/sales/partials/readiness-table.blade.php` with `context=customer reports`

KPIs show `—` with hint when core sources are not ready. Tabs remain accessible with placeholder states.

---

## Routes

| Method | URI | Name | Permission |
|--------|-----|------|------------|
| GET | `/admin/commercial/reports/customers` | `admin.commercial.reports.customers.index` | `admin.commercial.reports.customers.view` |
| POST | `/admin/commercial/reports/customers/export` | `admin.commercial.reports.customers.export` | `admin.commercial.reports.customers.export` |

**File:** `routes/admin_commercial.php`  
**Middleware:** `auth`, `verified`, `tenant`, `CaptureWorkspaceNavigationQuery`

---

## Controllers

| Class | Responsibility |
|-------|----------------|
| `App\Http\Controllers\Admin\Commercial\CommercialCustomerReportController` | Index presentation, queued export dispatch |

---

## Services

| Class | Responsibility |
|-------|----------------|
| `CommercialCustomerReportScope` | Immutable filter/scope DTO |
| `CommercialCustomerReportScopeResolver` | Resolves tenant scope, filter lists, permissions, readiness |
| `CommercialCustomerReportReadiness` | Data-source readiness assessment |
| `CommercialCustomerReportQueries` | Aggregate SQL queries — no collection reporting |
| `CommercialCustomerReportPresenter` | KPI cache, tab payload assembly |

---

## Queries (Operational Truth Only)

Reads from existing tables only:

- **Revenue orders:** `sales_orders` excluding `draft` and `cancelled`
- **Open quotes:** `quotations` in draft/pending/sent/viewed/accepted status
- **Open orders:** `sales_orders` in confirmed/ready/in-production/completed/on-hold
- **Salesperson:** `sales_orders.created_by` (proxy for salesperson attribution)
- **Lifetime value:** `SUM(total_amount)` per `customer_id` (all-time, branch-scoped)
- **Activity tab:** `customer_activities` when available; falls back to order events

**No duplicate or reporting-only tables created.**

### Tabs Implemented

1. Customer Summary — metrics + branch/salesperson breakdown  
2. New Customers — paginated (created in period)  
3. Active Customers — paginated (`status = active`)  
4. Inactive Customers — paginated (`status = inactive`)  
5. Customer Revenue — paginated by period revenue  
6. Customer Lifetime Value — paginated all-time value  
7. Customer Activity — paginated activities or orders  
8. Top Customers — Top 10/25/50 by revenue  
9. Customer Growth — monthly new customers + growth %  
10. Customers Without Recent Orders — no order in 90 days  
11. Customer By Branch — paginated  
12. Customer By Salesperson — paginated  

---

## KPIs

| KPI | Source |
|-----|--------|
| Total Customers | `customers` count (scoped filters) |
| New Customers | `created_at` in date range |
| Active Customers | `status = active` |
| Inactive Customers | `status = inactive` |
| Repeat Customers | 2+ revenue orders in period |
| Customer Growth % | New customers vs prior equal period |
| Average Customer Value | Period revenue / ordering customers |
| Top Customer Revenue | Max customer revenue in period |
| Customers With Open Quotes | Distinct customers with open quotations |
| Customers With Open Orders | Distinct customers with in-progress orders |

---

## Filters

| Filter | Query Param |
|--------|-------------|
| Date Range | `from_date`, `to_date` |
| Branch | `branch_id` |
| Customer Type | `customer_type` |
| Status | `status` |
| Salesperson | `salesperson_id` |
| Activity Status | `activity_status` (`active`, `inactive`, `new`, `dormant`) |
| Search | `search` |

**Session preservation:** `CaptureWorkspaceNavigationQuery` + `config/workspace_navigation.php`

---

## Permissions

| Permission | Purpose |
|------------|---------|
| `admin.commercial.reports.customers.view` | Access workspace |
| `admin.commercial.reports.customers.export` | Queue PDF/Excel/CSV exports |

**Seeder:** `database/seeders/RolesAndPermissionsSeeder.php`  
**Catalog:** `config/permission_catalog.php` → `commercial.customer_reports`

### Role Assignments

| Role | view | export |
|------|------|--------|
| Company Admin | ✓ | ✓ |
| Branch Manager | ✓ | ✓ |
| Sales | ✓ | — |

---

## Indexes

**Migration:** `2026_06_20_100000_add_customers_reporting_indexes.php`

### `customers`

- `customers_reporting_scope_idx` — `(company_id, branch_id, status)`
- `customers_reporting_type_idx` — `(company_id, customer_type, created_at)`
- `customers_reporting_created_idx` — `(company_id, created_at)`

### `customer_activities`

- `customer_activities_reporting_scope_idx` — `(company_id, branch_id, activity_at)`
- `customer_activities_reporting_customer_idx` — `(company_id, customer_id, activity_at)`

---

## Performance Controls

| Control | Implementation |
|---------|----------------|
| Pagination | 25 rows/page on tab tables via `LengthAwarePaginator` |
| Aggregate queries | `GROUP BY`, `SUM`, `COUNT`, `DISTINCT` in SQL |
| No N+1 | Order/quote stats via `LEFT JOIN` subqueries; names via `whereIn` pluck |
| No full load | No `Customer::all()` or collection aggregation |
| KPI cache | `PlatformCacheService` — 60s TTL, key includes filter hash |
| Blade | Presentation only — no heavy calculations in views |

---

## Exports (Queued Only)

| Format | Job | Output |
|--------|-----|--------|
| CSV | `ExportCommercialCustomerReportJob` | `storage/app/exports/commercial/customers/{company}/` |
| Excel | Same job | UTF-8 BOM TSV (`.xls`) |
| PDF | Same job | Printable HTML (`.html`) |

**Queue:** `exports` (via `PlatformJob::useQueue('exports')`)  
**No synchronous exports.**

---

## Navigation

**Workspace card:** `config/commercial_workspaces.php` → Reports → Customer Reports (live link)

---

## Tests Added

**File:** `tests/Feature/Commercial/CommercialCustomerReportTest.php`

| Test | Coverage |
|------|----------|
| `test_customer_reports_requires_permission` | 403 without permission |
| `test_customer_reports_index_loads` | Page, readiness, KPIs, tabs |
| `test_customer_reports_show_kpis_for_customers_and_orders` | Live KPI from factory data |
| `test_filters_persist_in_query_string` | Filter persistence |
| `test_export_requires_permission` | Export gated |
| `test_export_queues_job` | `Bus::fake` — job dispatched |

**Updated:** `tests/Feature/Commercial/CommercialWorkspaceTest.php` — Customer Reports linked.

---

## Files Created / Modified

### Created

- `app/Support/Commercial/Reports/CommercialCustomerReportScope.php`
- `app/Support/Commercial/Reports/CommercialCustomerReportScopeResolver.php`
- `app/Support/Commercial/Reports/CommercialCustomerReportReadiness.php`
- `app/Support/Commercial/Reports/CommercialCustomerReportQueries.php`
- `app/Support/Commercial/Reports/CommercialCustomerReportPresenter.php`
- `app/Http/Controllers/Admin/Commercial/CommercialCustomerReportController.php`
- `app/Jobs/Commercial/ExportCommercialCustomerReportJob.php`
- `resources/views/admin/commercial/reports/customers/**`
- `database/migrations/2026_06_20_100000_add_customers_reporting_indexes.php`
- `tests/Feature/Commercial/CommercialCustomerReportTest.php`
- `docs/PHASE_CR4_COMMERCIAL_CUSTOMER_REPORTS.md`

### Modified

- `routes/admin_commercial.php`
- `config/commercial_workspaces.php`
- `config/workspace_navigation.php`
- `config/permission_catalog.php`
- `database/seeders/RolesAndPermissionsSeeder.php`
- `resources/views/admin/commercial/reports/sales/partials/readiness-table.blade.php`
- `tests/Feature/Commercial/CommercialWorkspaceTest.php`

---

## Verification

```bash
php artisan migrate
php artisan test --filter=CommercialCustomerReportTest
```
