# PHASE CR-2 — Commercial Quotation Reports

## Implementation Report

**Module:** Commercial → Reports → Quotation Reports  
**Route:** `admin.commercial.reports.quotations.index`  
**Scope:** Commercial department quotation analytics — not Executive Intelligence.

---

## Readiness Table (Required First)

Rendered before KPIs/tabs via shared readiness partial.

| Source | Table | Required |
|--------|-------|----------|
| Quotations | `quotations` | Yes |
| Quotation Items | `quotation_items` | Yes |
| Customers | `customers` | Yes |
| Branches | `branches` | Yes |
| Users / Salespersons | `users` | Yes |

**Service:** `CommercialQuotationReportReadiness`

---

## Routes

| Method | URI | Name | Permission |
|--------|-----|------|------------|
| GET | `/admin/commercial/reports/quotations` | `admin.commercial.reports.quotations.index` | `admin.commercial.reports.quotations.view` |
| POST | `/admin/commercial/reports/quotations/export` | `admin.commercial.reports.quotations.export` | `admin.commercial.reports.quotations.export` |

---

## Controllers

- `CommercialQuotationReportController` — index + queued export

---

## Services

| Class | Role |
|-------|------|
| `CommercialQuotationReportScope` | Filter DTO |
| `CommercialQuotationReportScopeResolver` | Tenant scope + dropdowns |
| `CommercialQuotationReportReadiness` | Source readiness |
| `CommercialQuotationReportQueries` | Aggregate SQL on `quotations` / `quotation_items` |
| `CommercialQuotationReportPresenter` | KPI cache + tab payloads |

**No reporting-only tables. No duplicated quotation business logic.**

---

## KPI Metrics (9)

Quotes Issued · Quotes Accepted · Quotes Rejected · Quotes Expired · Average Quote Value · Total Quote Value · Accepted Quote Value · Conversion % · Average Approval Time

Cached 60s via `PlatformCacheService`.

---

## Filters

Date range · Branch · Customer · Salesperson · Status · Expiry status (valid / expiring soon / expired) · Search

Persisted via GET + `CaptureWorkspaceNavigationQuery`.

---

## Report Tabs (11)

1. Quotation Summary  
2. Open Quotations (paginated list)  
3. Expired Quotations (paginated)  
4. Accepted Quotations (paginated)  
5. Rejected Quotations (paginated)  
6. Quotation Value Analysis (value bands)  
7. Quotation Aging (open quote age buckets)  
8. Quote Win Rate (won/loss breakdown)  
9. Quotation By Customer (paginated)  
10. Quotation By Salesperson (paginated)  
11. Quotation By Branch  

---

## Exports (Queued Only)

CSV · Excel (TSV) · PDF (HTML) via `ExportCommercialQuotationReportJob` on `exports` queue.

---

## Permissions

| Permission | Roles |
|------------|-------|
| `admin.commercial.reports.quotations.view` | Company Admin, Branch Manager, Sales |
| `admin.commercial.reports.quotations.export` | Company Admin, Branch Manager |

---

## Performance

- Aggregate `GROUP BY` queries  
- Pagination (25/page) on list and breakdown tabs  
- Eager-load `customer` + `preparer` on quote lists (no N+1)  
- KPI cache with filter hash key  
- Indexes from CR-1 migration on `quotations`  

---

## Tests

`tests/Feature/Commercial/CommercialQuotationReportTest.php` — 6 tests

---

## Verification

```bash
php artisan test --filter=CommercialQuotationReportTest
```
