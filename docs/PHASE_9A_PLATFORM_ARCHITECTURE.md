# Phase 9A — Platform Performance & Architecture

Master coordinator approved hardening pass. No new business modules were added.

## 1. Database performance review

### Findings

| Area | Status | Notes |
|------|--------|-------|
| Tenant columns | Good | Most headers use `company_id` / `branch_id` |
| Composite indexes | **Added** | High-traffic list/filter tables now indexed |
| Status/date indexes | **Added** | Quotations, orders, job cards, leads, stock docs |
| Soft deletes | None | Not used anywhere (by design today) |
| Child tables without tenant cols | Acceptable | Isolated via parent FK |
| `activity_logs` | **Hardened** | Added `company_id` + tenant scope |
| N+1 (inventory dashboard) | **Fixed** | Single aggregated balance query |
| Nullable FK risks | Low | Standard Laravel `nullOnDelete` patterns |

### Index migration

`database/migrations/2026_06_10_100001_add_platform_performance_indexes.php`

Adds composite `(company_id, branch_id, status)` and date indexes on:

- quotations, sales_orders, production_job_cards, artwork_requests
- leads, customers, stock_receipts/issues/adjustments, production_queues
- lead_follow_ups, inventory_movements, inventory_reorder_alerts
- activity_logs `(company_id, created_at)`

Indexes were added only where tenant-scoped list/dashboard queries are common — not on every FK.

## 2. Query performance standards

See `docs/PERFORMANCE_STANDARDS.md`.

Enforced in this phase:

- Roles, permissions, work centers, activity logs now paginate
- Inventory dashboard uses `InventoryStockService::branchBalancesMap()`
- Main dashboard metrics cached (60s TTL)
- Activity logs tenant-scoped

## 3. Queue architecture

- Config: `config/platform.php` → `queues` array
- Base job: `app/Jobs/PlatformJob.php` (implements `ShouldQueue`, database-ready)
- Default driver: `database` (`config/queue.php`)
- Named queues: default, notifications, reports, documents, emails, sms, imports, exports, integrations
- Switch `QUEUE_CONNECTION=redis` later without changing job assignments

## 4. Cache architecture

- Config: `config/platform.php` → `cache` TTL categories
- Service: `App\Support\Platform\PlatformCacheService`
- Cached today: main dashboard metrics, filtered navigation menu
- Defined (wire as needed): permissions, settings, branches, departments, lead stages, quotation statuses, inventory categories
- See `docs/QUEUE_AND_CACHE.md` for examples
- **Do not cache** live stock balances without explicit invalidation

## 5. Page speed / UI loading

**Chosen approach: Hotwire Turbo (Option A)** — Turbo Drive + Turbo Frame `erp-main`.

| Option | Decision |
|--------|----------|
| A — Turbo | **Implemented** |
| B — Alpine partials | Not used (more custom fetch/CSRF wiring) |
| C — htmx | Not used (less native Laravel session/redirect ergonomics) |

### Behaviour

- Sidebar and topbar stay mounted; only `#erp-main` frame swaps on navigation.
- Sidebar links use `data-turbo-frame="erp-main"`.
- File uploads and tenant context switch use `data-turbo-frame="_top"` for full-page POST.
- Alpine re-inits inside the frame on `turbo:frame-load`; nav active state syncs via `#erp-route-meta`.
- Filtered navigation menu cached per user/company/branch/roles (`PlatformCacheService`).

Package: `@hotwired/turbo` via Vite (`resources/js/app.js`).

## 6. Form control architecture

Tables:

- `system_settings` — key/value with company/branch scope
- `form_settings` — form definitions per tenant
- `form_field_settings` — required/visible/hidden/default per field
- `numbering_sequences` — atomic document numbering
- `approval_rules` — configurable approval thresholds

Services:

- `SystemSettingsService` — branch → company → global fallback
- `FormSettingsService` — field rules retrieval
- `ApprovalRulesService` — threshold checks

Seeder: `PlatformConfigurationSeeder`

## 7. Numbering system

- `App\Support\Platform\NumberingService`
- Format: `JANA-HQ-QUOTE-2026-00001` via `{company}-{branch}-{type}-{year}-{number}`
- Wired into: customers, quotations, artwork, sales orders, job cards, stock receipts/issues/adjustments
- Atomic via `lockForUpdate()` on `numbering_sequences`

## 8. Approval rules foundation

- `approval_rules` table + `ApprovalRulesService`
- Types: quotation, discount, stock adjustment, procurement, payment
- Seeded defaults for JANA company (not yet wired into existing quotation workflow — foundation only)

## 9. Tests

`tests/Feature/Platform/PlatformArchitectureTest.php`

## Risks found

1. **Existing document numbers** differ from new format (e.g. `QUO-00001` vs `JANA-HQ-QUOTE-2026-00001`) — new records use centralized format only
2. **Super Admin activity logs** without company context still see all logs (intentional)
3. **Form dropdown `->get()`** on create/edit forms still loads full lists — acceptable for now; move to search/autocomplete in a later phase
4. **Module dashboards** still run multiple count queries — cache per module recommended next
5. **Redis not configured** — database queue/cache works for dev; plan Redis before production load

## Recommendations before continuing modules

1. Wire `ApprovalRulesService` into quotation discount/submit flows
2. Build System Setup UI for settings/forms/numbering (read services already exist)
3. Add autocomplete endpoints for customer/lead/item selects
4. Cache module dashboard KPIs
5. Move to Redis queue + cache in staging
6. Backfill `activity_logs.company_id` for historical rows
7. Consider global tenant scope middleware to reduce manual `forTenant()` calls
