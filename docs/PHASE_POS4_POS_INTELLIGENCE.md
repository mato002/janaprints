# PHASE POS-4 — POS Intelligence

## Implementation Report

**Module:** Commercial → Point Of Sale → POS Intelligence  
**Route:** `commercial.pos.reports.index`  
**Audience:** Branch Manager, Sales, Cashiers (view), Company Admin (view + export)  
**Scope:** Departmental POS operational intelligence — not Executive Intelligence.

---

## Readiness Table (Required First)

Displayed at the top of the POS Intelligence workspace before dashboard KPIs and report tabs.

| Source | Table | Required Columns | Status Check |
|--------|-------|------------------|--------------|
| POS Sales | `pos_sales` | `company_id`, `branch_id`, `cashier_id`, `pos_session_id`, `sale_number`, `sale_date`, `status`, `total_amount`, `created_at` | `Schema::hasTable` + column check |
| POS Payments | `pos_payments` | `pos_sale_id`, `payment_method`, `amount` | Same |
| POS Sale Items | `pos_sale_items` | `pos_sale_id`, `quantity`, `line_total` | Optional — basket size |
| POS Sessions | `pos_sessions` | `company_id`, `branch_id`, `cashier_id`, `session_number`, `status`, `opening_float`, `opening_cash`, `expected_cash`, `actual_cash`, `variance`, `opened_at`, `closed_at` | Same |
| POS Returns | `pos_returns` | `company_id`, `branch_id`, `pos_sale_id`, `return_number`, `status`, `refund_method`, `refund_amount`, `completed_at`, `created_at` | Optional — preferred for returns/refunds |
| Branches | `branches` | `company_id`, `name`, `is_active` | Same |
| Cashiers | `users` | `company_id`, `name` | Same |

**Service:** `App\Support\Commercial\Reports\CommercialPosReportReadiness`  
**View:** Reuses `resources/views/admin/commercial/reports/sales/partials/readiness-table.blade.php`

KPIs show `—` with hint when core sources are not ready. Returns metrics fall back to `pos_sales.status = refunded` when `pos_returns` is unavailable.

---

## Routes

| Method | URI | Name | Permission |
|--------|-----|------|------------|
| GET | `/admin/commercial/pos/intelligence` | `commercial.pos.reports.index` | `commercial.pos.reports.view` |
| POST | `/admin/commercial/pos/intelligence/export` | `commercial.pos.reports.export` | `commercial.pos.reports.export` |

**File:** `routes/admin_commercial.php`  
**Middleware:** `auth`, `verified`, `tenant`, `CaptureWorkspaceNavigationQuery`

---

## Controllers & Services

| Class | Responsibility |
|-------|----------------|
| `CommercialPosReportController` | Index presentation, queued export dispatch |
| `CommercialPosReportScope` | Immutable filter/scope DTO |
| `CommercialPosReportScopeResolver` | Tenant scope, filter lists, permissions, readiness |
| `CommercialPosReportReadiness` | Data-source readiness assessment |
| `CommercialPosReportQueries` | Aggregate SQL queries — no collection reporting |
| `CommercialPosReportPresenter` | Dashboard KPIs, operational metrics, tab payloads |

---

## Dashboard KPIs

| KPI | Source |
|-----|--------|
| Today's Sales | Paid `pos_sales` today (value + count) |
| Today's Returns | Completed `pos_returns` today, or refunded sales |
| Open Sessions | `pos_sessions` with `status = open` |
| Closed Sessions | Sessions closed in filter period |
| Cash Collected | `pos_payments` cash on paid sales in period |
| M-Pesa Collected | `pos_payments` mpesa on paid sales in period |
| Card Collected | `pos_payments` card on paid sales in period |
| Average Sale Value | AVG `total_amount` on paid sales in period |

---

## Operational Metrics

| Metric | Source |
|--------|--------|
| Top Cashier | Highest revenue cashier in period |
| Top Branch | Highest revenue branch in period |
| Average Basket Size | AVG item quantity per paid sale |
| Return Rate | Returns ÷ (paid + returns) |
| Refund Value | Sum refund amounts in period |
| Sales Trend | Period revenue vs prior equal-length period |

---

## Reports (Tabs)

1. **Sales By Cashier** — sales count, revenue, avg sale per cashier  
2. **Sales By Branch** — sales count, revenue, avg sale per branch  
3. **Sales By Day** — daily paid sales trend  
4. **Sales By Hour** — hourly paid sales distribution  
5. **Returns Analysis** — daily return count, value, rate  
6. **Refund Analysis** — refund method breakdown  
7. **Session Performance** — session sales, revenue, variance  
8. **Payment Method Analysis** — tender mix and share  

---

## Filters

`from_date`, `to_date`, `branch_id`, `cashier_id`, `payment_method`, `status`, `search`, `tab`, `page`

Branch scoping follows tenant branch unless user has `commercial.pos.sessions.admin`.

---

## Permissions

| Permission | Purpose |
|------------|---------|
| `commercial.pos.reports.view` | Access POS Intelligence workspace |
| `commercial.pos.reports.export` | Queue PDF/Excel/CSV exports |

**Seeder:** `database/seeders/RolesAndPermissionsSeeder.php`  
**Catalog:** `config/permission_catalog.php` → `commercial.pos` extras

### Role Assignments

| Role | view | export |
|------|------|--------|
| Company Admin | ✓ | ✓ |
| Branch Manager | ✓ | ✓ |
| Sales | ✓ | — |

---

## Indexes

**Migration:** `2026_06_25_100000_add_pos_intelligence_reporting_indexes.php`

- `pos_sales_intel_scope_idx` — `(company_id, branch_id, sale_date)`
- `pos_sales_intel_status_idx` — `(company_id, status, sale_date)`
- `pos_sales_intel_cashier_idx` — `(company_id, cashier_id, sale_date)`
- `pos_sales_intel_session_idx` — `(pos_session_id, status)`
- `pos_payments_intel_method_idx` — `(pos_sale_id, payment_method)`
- `pos_sessions_intel_scope_idx` — `(company_id, branch_id, status, closed_at)`
- `pos_returns_intel_scope_idx` — `(company_id, branch_id, status, completed_at)`

---

## Performance Controls

| Control | Implementation |
|---------|----------------|
| Pagination | 25 rows/page |
| Aggregate queries | `GROUP BY`, `COUNT`, `SUM`, `AVG` in SQL |
| No N+1 | Batch session metrics; name maps via `whereIn` pluck |
| KPI cache | `PlatformCacheService` — 60s TTL |
| SQLite tests | `julianday` / `strftime` fallbacks for date/hour |

---

## Exports (Queued Only)

| Format | Job | Registry Module |
|--------|-----|-----------------|
| CSV | `ProcessCommercialReportExportJob` | `pos` |
| Excel | Same | Same |
| PDF | Same | Same |

**Exporter:** `PosReportExporter`  
**Queue:** `exports`

---

## Navigation

**Workspace card:** `config/commercial_workspaces.php` → Point Of Sale → POS Intelligence

---

## Tests

**File:** `tests/Feature/Commercial/CommercialPosReportTest.php`

| Test | Coverage |
|------|----------|
| `test_pos_intelligence_requires_permission` | 403 without permission |
| `test_pos_intelligence_index_loads` | Page, readiness, dashboard, tabs |
| `test_report_accuracy_for_paid_sales` | Live KPI from paid sale |
| `test_filter_accuracy_by_cashier` | Cashier filter + query accuracy |
| `test_export_requires_permission` | Export gated |
| `test_export_queues_job` | Queued export with module `pos` |
| `test_branch_scoping_hides_other_branch_sales` | Tenant branch isolation |
| `test_performance_validation_uses_aggregate_queries` | ≤5 queries for cashier aggregate |

---

## Verification

```bash
php artisan migrate
php artisan test --filter=CommercialPosReportTest
```

---

## Files Created

- `app/Support/Commercial/Reports/CommercialPosReport*.php` (5 classes)
- `app/Support/Commercial/Reports/Exports/Exporters/PosReportExporter.php`
- `app/Http/Controllers/Admin/Commercial/CommercialPosReportController.php`
- `resources/views/admin/commercial/pos/intelligence/**`
- `database/migrations/2026_06_25_100000_add_pos_intelligence_reporting_indexes.php`
- `tests/Feature/Commercial/CommercialPosReportTest.php`
- `docs/PHASE_POS4_POS_INTELLIGENCE.md`

## Files Modified

- `routes/admin_commercial.php`
- `config/commercial_workspaces.php`
- `config/permission_catalog.php`
- `database/seeders/RolesAndPermissionsSeeder.php`
- `app/Support/Commercial/Reports/Exports/CommercialReportExportRegistry.php`
