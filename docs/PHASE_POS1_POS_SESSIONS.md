# PHASE POS-1 — POS Sessions Foundation

## Implementation Report

**Module:** Commercial → Point Of Sale → POS Sessions  
**Route:** `admin.commercial.pos.sessions.index`  
**Scope:** Cashier session control — does not rebuild Counter Sales or duplicate sales logic.

---

## Readiness Table (Required First)

| Source | Table | Required Columns | Status Check |
|--------|-------|------------------|--------------|
| POS Sessions | `pos_sessions` | `company_id`, `branch_id`, `cashier_id`, `session_number`, `opening_float`, `opening_cash`, `expected_cash`, `actual_cash`, `variance`, `status`, `opened_at`, `closed_at` | `Schema::hasTable` + column check |
| Counter Sales | `pos_sales` | `company_id`, `branch_id`, `cashier_id`, `pos_session_id`, `sale_number`, `sale_date`, `status`, `total_amount` | Same |
| POS Payments | `pos_payments` | `pos_sale_id`, `payment_method`, `amount` | Same |
| Branches | `branches` | `company_id`, `name`, `is_active` | Same |
| Cashiers | `users` | `company_id`, `name` | Same |

**Service:** `App\Support\Commercial\PosSessionReadiness`  
**View:** Reuses `resources/views/admin/commercial/reports/sales/partials/readiness-table.blade.php`

---

## Routes

| Method | URI | Name | Permission |
|--------|-----|------|------------|
| GET | `/admin/commercial/pos/sessions` | `admin.commercial.pos.sessions.index` | `commercial.pos.sessions.view` |
| GET | `/admin/commercial/pos/sessions/open` | `admin.commercial.pos.sessions.create` | `commercial.pos.sessions.open` |
| POST | `/admin/commercial/pos/sessions` | `admin.commercial.pos.sessions.store` | `commercial.pos.sessions.open` |
| GET | `/admin/commercial/pos/sessions/{session}` | `admin.commercial.pos.sessions.show` | `commercial.pos.sessions.view` |
| GET | `/admin/commercial/pos/sessions/{session}/close` | `admin.commercial.pos.sessions.close` | `commercial.pos.sessions.close` |
| POST | `/admin/commercial/pos/sessions/{session}/close` | `admin.commercial.pos.sessions.close.store` | `commercial.pos.sessions.close` |

---

## Session Workflow

| Action | Implementation |
|--------|----------------|
| Open Session | `PosSessionService::openSession()` — float, opening cash, cashier, branch |
| Close Session | `PosSessionService::closeSession()` — expected/actual/variance |
| Cashier Assignment | `cashier_id` on session |
| Branch Assignment | Tenant branch on open |
| Expected Cash | Opening float + cash − cash refunds from session sales |
| Actual Cash | Entered at close |
| Variance | `actual_cash − expected_cash` |

### Statuses

`open` · `closed` · `suspended` · `cancelled` (enum ready; open/close implemented)

### Session Rules

- One active session per cashier per branch (`open` or `suspended`)
- Counter sales require an open session (`pos_session_id` on `pos_sales`)
- Closed sessions reject new sales
- Close requires `commercial.pos.sessions.close`
- `commercial.pos.sessions.admin` sees all company branches; others branch-scoped

---

## Permissions

| Permission | Purpose |
|------------|---------|
| `commercial.pos.sessions.view` | Sessions index and detail |
| `commercial.pos.sessions.open` | Open new sessions |
| `commercial.pos.sessions.close` | Close sessions |
| `commercial.pos.sessions.audit` | View audit trail on detail |
| `commercial.pos.sessions.admin` | Cross-branch session visibility |

### Role Assignments

| Role | view | open | close | audit | admin |
|------|------|------|-------|-------|-------|
| Company Admin | ✓ | ✓ | ✓ | ✓ | ✓ |
| Branch Manager | ✓ | ✓ | ✓ | ✓ | — |
| Sales | ✓ | ✓ | — | — | — |

---

## UI Pages

- **Sessions Index** — KPI strip, filters, session table
- **Open Session Form** — cashier, float, opening cash
- **Close Session Form** — expected cash, actual count, notes
- **Session Detail** — metrics, sales list, audit trail

Counter Sales create form shows active session requirement; POS dashboard links to sessions.

---

## Tests

**File:** `tests/Feature/Commercial/PosSessionTest.php`

| Test | Coverage |
|------|----------|
| `test_open_session` | Session created |
| `test_prevent_duplicate_open_session` | Validation error |
| `test_attach_sale_to_session` | `pos_session_id` set |
| `test_close_session` | Status closed, variance stored |
| `test_block_sales_after_close` | No open session error |
| `test_branch_scoping` | 404 across branches |
| `test_permission_enforcement` | 403 without permission |

**Updated:** `CommercialWorkspaceTest` — sale creation opens session first

---

## Verification

```bash
php artisan migrate
php artisan test --filter=PosSessionTest
php artisan test --filter=CommercialWorkspaceTest
```

**Results:** `PosSessionTest` 7/7 passed · `CommercialWorkspaceTest` 10/10 passed

---

## Implementation Notes

- **Middleware priority:** `SetTenantContext` runs before `SubstituteBindings` so tenant-scoped route model binding works for `{session}` routes.
- **Counter Sales integration:** `PosSaleController` injects `PosSessionService`; sales require an open session via `pos_session_id`.
- **Workspace access:** Point Of Sale hub/section requires `pos.view|commercial.pos.sessions.view`.
