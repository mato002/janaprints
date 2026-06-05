# PHASE CR-5 — Commercial Artwork Reports

## Implementation Report

**Module:** Commercial → Reports → Artwork Reports  
**Route:** `commercial.reports.artwork.index`  
**Audience:** Commercial Manager, Branch Manager, Sales, Designers  
**Scope:** Commercial artwork reporting only — reads operational artwork tables; no Production module changes.

---

## Readiness Table (Required First)

Displayed at the top of the Artwork Reports workspace before KPIs and tabs.

| Source | Table | Required Columns | Status Check |
|--------|-------|------------------|--------------|
| Artwork Requests | `artwork_requests` | `company_id`, `branch_id`, `customer_id`, `quotation_id`, `requested_by`, `assigned_designer_id`, `priority`, `status`, `due_date`, `current_version`, `created_at` | `Schema::hasTable` + column check |
| Artwork Approvals | `artwork_approvals` | `company_id`, `branch_id`, `artwork_request_id`, `artwork_version_id`, `approved_by`, `decision`, `created_at` | Same |
| Artwork Versions | `artwork_versions` | `artwork_request_id`, `version_number`, `uploaded_by`, `created_at` | Same |
| Customers | `customers` | `company_id`, `branch_id`, `company_name`, `customer_code` | Same |
| Branches | `branches` | `company_id`, `name`, `is_active` | Same |
| Users / Designers | `users` | `company_id`, `name` | Same |
| Quotations (optional) | `quotations` | `company_id`, `customer_id`, `status` | Optional |
| Sales Orders (optional) | `sales_orders` | `company_id`, `customer_id`, `status` | Optional |

**Service:** `App\Support\Commercial\Reports\CommercialArtworkReportReadiness`  
**View:** Reuses `resources/views/admin/commercial/reports/sales/partials/readiness-table.blade.php`

KPIs show `—` with hint when core sources are not ready. Tabs remain accessible with placeholder states.

---

## Routes

| Method | URI | Name | Permission |
|--------|-----|------|------------|
| GET | `/admin/commercial/reports/artwork` | `commercial.reports.artwork.index` | `commercial.reports.artwork.view` |
| POST | `/admin/commercial/reports/artwork/export` | `commercial.reports.artwork.export` | `commercial.reports.artwork.export` |

**File:** `routes/admin_commercial.php`  
**Middleware:** `auth`, `verified`, `tenant`, `CaptureWorkspaceNavigationQuery`

---

## Controllers

| Class | Responsibility |
|-------|----------------|
| `App\Http\Controllers\Admin\Commercial\CommercialArtworkReportController` | Index presentation, queued export dispatch |

---

## Services

| Class | Responsibility |
|-------|----------------|
| `CommercialArtworkReportScope` | Immutable filter/scope DTO |
| `CommercialArtworkReportScopeResolver` | Resolves tenant scope, filter lists, permissions, readiness |
| `CommercialArtworkReportReadiness` | Data-source readiness assessment |
| `CommercialArtworkReportQueries` | Aggregate SQL queries — no collection reporting |
| `CommercialArtworkReportPresenter` | KPI cache, tab payload assembly |

---

## Reports (Tabs)

1. **Artwork Requests** — all requests in period (paginated)  
2. **Artwork Pending** — `requested`, `in_design`, `submitted`, `revision_requested`  
3. **Artwork Approved** — `approved` status  
4. **Artwork Rejected** — `rejected` status  
5. **Artwork Turnaround Time** — request created → first approval  
6. **Designer Performance** — assigned / completed / pending per designer  
7. **Revision Analysis** — version counts and revision depth  
8. **Artwork By Customer** — grouped customer metrics  
9. **Artwork By Branch** — grouped branch metrics  
10. **Artwork Delays** — overdue open requests (`due_date` passed, not terminal)

---

## KPIs

| KPI | Source |
|-----|--------|
| Total Artwork Requests | Count in scope |
| Pending Artwork | Non-terminal pipeline statuses |
| Approved Artwork | `status = approved` |
| Rejected Artwork | `status = rejected` |
| Average Approval Time | `artwork_requests` → first `artwork_approvals` (approved) |
| Average Revision Count | `artwork_versions` count (fallback: `current_version`) |
| Designer Throughput | Approved requests with assigned designer |
| Delayed Artwork | Past `due_date`, not approved/rejected |
| Artwork Approval Rate | approved ÷ (approved + rejected) |

---

## Filters

`from_date`, `to_date`, `branch_id`, `customer_id`, `designer_id`, `status`, `approval_status`, `delay_status`, `search`, `tab`, `page`

---

## Permissions

| Permission | Purpose |
|------------|---------|
| `commercial.reports.artwork.view` | Access workspace |
| `commercial.reports.artwork.export` | Queue PDF/Excel/CSV exports |

**Seeder:** `database/seeders/RolesAndPermissionsSeeder.php`  
**Catalog:** `config/permission_catalog.php` → `commercial.artwork_reports`

### Role Assignments

| Role | view | export |
|------|------|--------|
| Company Admin | ✓ | ✓ |
| Branch Manager | ✓ | ✓ |
| Sales | ✓ | — |
| Designer | ✓ | — |

---

## Indexes

**Migration:** `2026_06_19_120000_add_artwork_requests_reporting_indexes.php`

### `artwork_requests`

- `artwork_requests_reporting_scope_idx` — `(company_id, branch_id, created_at)`
- `artwork_requests_reporting_status_idx` — `(company_id, status, created_at)`
- `artwork_requests_reporting_customer_idx` — `(company_id, customer_id, created_at)`
- `artwork_requests_reporting_designer_idx` — `(company_id, assigned_designer_id, status)`
- `artwork_requests_reporting_due_idx` — `(company_id, due_date, status)`

### `artwork_approvals`

- `artwork_approvals_reporting_decision_idx` — `(company_id, artwork_request_id, decision)`

---

## Performance Controls

| Control | Implementation |
|---------|----------------|
| Pagination | 25 rows/page via `LengthAwarePaginator` |
| Aggregate queries | `GROUP BY`, `COUNT`, `AVG` in SQL |
| No N+1 | Customer/branch/designer names via `whereIn` pluck maps |
| KPI cache | `PlatformCacheService` — 60s TTL |
| SQLite tests | `julianday` fallback for hour diffs |

---

## Exports (Queued Only)

| Format | Job | Output |
|--------|-----|--------|
| CSV | `ExportCommercialArtworkReportJob` | `storage/app/exports/commercial/artwork/{company}/` |
| Excel | Same job | UTF-8 BOM TSV (`.xls`) |
| PDF | Same job | Printable HTML (`.html`) |

**Queue:** `exports`

---

## Navigation

**Workspace card:** `config/commercial_workspaces.php` → Reports → Artwork Reports (live link)

---

## Tests

**File:** `tests/Feature/Commercial/CommercialArtworkReportTest.php`

| Test | Coverage |
|------|----------|
| `test_artwork_reports_requires_permission` | 403 without permission |
| `test_artwork_reports_index_loads` | Page, readiness, KPIs, tabs |
| `test_artwork_reports_show_kpis_for_requests` | Live KPI from factory request |
| `test_filters_persist_in_query_string` | Filter persistence |
| `test_export_requires_permission` | Export gated |
| `test_export_queues_job` | `Bus::fake` — job dispatched |

**Updated:** `tests/Feature/Commercial/CommercialWorkspaceTest.php` — Artwork Reports linked.

---

## Verification

```bash
php artisan migrate
php artisan test --filter=CommercialArtworkReportTest
```

**Result:** 6/6 tests passed.

---

## Files Created

- `app/Support/Commercial/Reports/CommercialArtworkReport*.php` (5 classes)
- `app/Http/Controllers/Admin/Commercial/CommercialArtworkReportController.php`
- `app/Jobs/Commercial/ExportCommercialArtworkReportJob.php`
- `resources/views/admin/commercial/reports/artwork/**`
- `database/migrations/2026_06_19_120000_add_artwork_requests_reporting_indexes.php`
- `tests/Feature/Commercial/CommercialArtworkReportTest.php`
- `docs/PHASE_CR5_COMMERCIAL_ARTWORK_REPORTS.md`

## Files Modified

- `routes/admin_commercial.php`
- `config/commercial_workspaces.php`
- `config/permission_catalog.php`
- `database/seeders/RolesAndPermissionsSeeder.php`
- `tests/Feature/Commercial/CommercialWorkspaceTest.php`
