# PHASE CR-6 — Commercial Conversion Reports

## Implementation Report

**Module:** Commercial → Reports → Conversion Reports  
**Route:** `admin.commercial.reports.conversion.index`  
**Audience:** Sales Team, Commercial Manager, Branch Sales Manager  
**Scope:** Departmental commercial funnel conversion — not Executive Intelligence or Executive 360.

---

## Readiness Table (Required First)

Displayed at the top of the Conversion Reports workspace before KPIs and funnel tabs.

| Source | Table | Required Columns | Status Check |
|--------|-------|------------------|--------------|
| Leads | `leads` | `company_id`, `branch_id`, `lead_source_id`, `assigned_to`, `customer_id`, `status`, `created_at` | `Schema::hasTable` + column check |
| Customers | `customers` | `company_id`, `branch_id`, `customer_type`, `company_name` | Same |
| Quotations | `quotations` | `company_id`, `branch_id`, `customer_id`, `prepared_by`, `status`, `quotation_date`, `quotation_number` | Same |
| Sales Orders | `sales_orders` | `company_id`, `branch_id`, `customer_id`, `created_by`, `status`, `order_date`, `order_number` | Same |
| Artwork Requests | `artwork_requests` | `company_id`, `branch_id`, `customer_id`, `quotation_id`, `status`, `created_at` | Optional |
| Production Job Cards | `production_job_cards` | `company_id`, `branch_id`, `sales_order_id`, `customer_id`, `status`, `created_at` | Optional — read-only production stage |
| Delivery Notes | `delivery_notes` | `company_id`, `branch_id`, `sales_order_id`, `status`, `delivery_date`, `dispatched_at`, `delivered_at` | Optional — read-only dispatch/delivery stages |
| Lead Sources | `lead_sources` | `company_id`, `name`, `is_active` | Same |
| Branches | `branches` | `company_id`, `name`, `is_active` | Same |
| Users / Salespersons | `users` | `company_id`, `name` | Same |

**Service:** `App\Support\Commercial\Reports\CommercialConversionReportReadiness`  
**View:** Reuses `resources/views/admin/commercial/reports/sales/partials/readiness-table.blade.php`

KPIs show `—` with hint when core sources are not ready. Production/dispatch stages show zero with an amber notice when optional pipeline tables are unavailable.

---

## Routes

| Method | URI | Name | Permission |
|--------|-----|------|------------|
| GET | `/admin/commercial/reports/conversion` | `admin.commercial.reports.conversion.index` | `admin.commercial.reports.conversion.view` |
| POST | `/admin/commercial/reports/conversion/export` | `admin.commercial.reports.conversion.export` | `admin.commercial.reports.conversion.export` |

**File:** `routes/admin_commercial.php`  
**Middleware:** `auth`, `verified`, `tenant`, `CaptureWorkspaceNavigationQuery`

---

## Controllers & Services

| Class | Responsibility |
|-------|----------------|
| `CommercialConversionReportController` | Index presentation, queued export dispatch |
| `CommercialConversionReportScope` | Immutable filter/scope DTO |
| `CommercialConversionReportScopeResolver` | Tenant scope, filter lists, permissions, readiness |
| `CommercialConversionReportReadiness` | Data-source readiness assessment |
| `CommercialConversionReportQueries` | Aggregate SQL funnel queries — no collection reporting |
| `CommercialConversionReportPresenter` | KPI cache, funnel tab payload assembly |

---

## Funnel Stages (Operational Truth)

| Stage | Source | Date Column | Notes |
|-------|--------|-------------|-------|
| Leads | `leads` | `created_at` | All leads in period |
| Quotes | `quotations` | `quotation_date` | Excludes `draft` |
| Orders | `sales_orders` | `order_date` | Excludes `draft`, `cancelled` |
| Production | `production_job_cards` | `created_at` | Excludes `draft`, `cancelled` — read-only |
| Dispatch | `delivery_notes` | `delivery_date` | `dispatched` or `delivered` status |
| Delivered | `delivery_notes` | `delivery_date` | `delivered` status only |

**No duplicate or reporting-only tables created.**

### Funnel Reports (Tabs)

1. Full Commercial Funnel  
2. Lead → Quote  
3. Quote → Order  
4. Order → Production  
5. Production → Dispatch  
6. Dispatch → Delivery  

Each tab shows funnel cards, stage drop-off table, branch conversion table, and salesperson conversion table.

---

## KPIs

| KPI | Calculation |
|-----|-------------|
| Lead-to-Quote % | Quotes / Leads |
| Quote-to-Order % | Orders / Quotes |
| Order-to-Production % | Production / Orders |
| Production-to-Dispatch % | Dispatch / Production |
| Dispatch-to-Delivery % | Delivered / Dispatch |
| Total Funnel Drop-Off | (1 − Delivered / Leads) × 100 |
| Best Converting Branch | Highest quote-to-order % by branch |
| Best Converting Salesperson | Highest quote-to-order % by salesperson |

---

## Filters

`from_date`, `to_date`, `branch_id`, `salesperson_id`, `lead_source_id`, `customer_type`, `status` (lead status), `search`, `tab`

---

## Permissions

| Permission | Purpose |
|------------|---------|
| `admin.commercial.reports.conversion.view` | Access workspace |
| `admin.commercial.reports.conversion.export` | Queue PDF/Excel/CSV exports |

### Role Assignments

| Role | view | export |
|------|------|--------|
| Company Admin | ✓ | ✓ |
| Branch Manager | ✓ | ✓ |
| Sales | ✓ | — |

---

## Indexes

**Migration:** `2026_06_21_100000_add_conversion_reporting_indexes.php`

### `leads`

- `leads_reporting_scope_idx` — `(company_id, branch_id, created_at)`
- `leads_reporting_source_idx` — `(company_id, lead_source_id, created_at)`
- `leads_reporting_assignee_idx` — `(company_id, assigned_to, created_at)`

### `production_job_cards`

- `production_job_cards_reporting_scope_idx` — `(company_id, branch_id, created_at)`

### `delivery_notes`

- `delivery_notes_reporting_scope_idx` — `(company_id, branch_id, delivery_date)`
- `delivery_notes_reporting_status_idx` — `(company_id, status, delivery_date)`

Reuses existing quotation and sales order reporting indexes from CR-1/CR-3.

---

## Performance Controls

| Control | Implementation |
|---------|----------------|
| Aggregate queries | `COUNT`, `GROUP BY` per branch/salesperson in SQL |
| No N+1 | Branch/user names via `whereIn` pluck maps |
| No full load | No `::all()` or collection aggregation |
| KPI cache | `PlatformCacheService` — 60s TTL |
| Blade | Presentation only |

---

## Exports (Queued Only)

| Format | Job | Output |
|--------|-----|--------|
| CSV | `ExportCommercialConversionReportJob` | `storage/app/exports/commercial/conversion/{company}/` |
| Excel | Same job | UTF-8 BOM TSV (`.xls`) |
| PDF | Same job | Printable HTML (`.html`) |

**Queue:** `exports`

---

## Tests

**File:** `tests/Feature/Commercial/CommercialConversionReportTest.php` — 6 tests

**Updated:** `tests/Feature/Commercial/CommercialWorkspaceTest.php` — Conversion Reports linked

---

## Verification

```bash
php artisan migrate
php artisan test --filter=CommercialConversionReportTest
```
