# Workspace Ownership & Routing Guide

**Phase:** ADMIN-P0 — Security & Governance Hardening  
**Date:** 2026-06-06

This document clarifies which ERP workspaces own which configuration and operational surfaces. Administration is the **governance backbone**; other workspaces own domain-specific setup and day-to-day operations.

---

## Routing Model

| Layer | Config | Route prefix | Purpose |
|-------|--------|--------------|---------|
| Workspace hub | `config/workspaces.php` | `admin/workspaces/{workspace}` | Top-level ERP modules in the sidebar |
| Section hub | `config/*_workspaces.php` | `admin/workspaces/{workspace}/{section}` | Grouped features within large workspaces |
| Feature routes | `routes/admin_*.php` | `admin/...` | Live CRUD, dashboards, and settings screens |
| Settings Control Center | `config/settings_control_center.php` | `admin/settings` (hub section) | Cross-workspace settings discovery |

All admin routes require `auth`, `admin.auth`, `verified`, and `tenant` middleware unless noted otherwise. The `admin.auth` middleware blocks client-portal sessions (`auth_context === 'client'`) from reaching ERP admin surfaces.

---

## Ownership Boundaries

### Administration

**Owns:** Identity, RBAC, org structure, ERP-wide behavioral settings, workflow governance, integration credentials, and system operations.

| Hub | Route file(s) | Canonical entry |
|-----|---------------|-----------------|
| Security & Access | `routes/admin.php` | `admin.workspaces.administration.section` → `security-access` |
| Organization | `routes/admin.php` | `admin.employees.index` (master CRUD); lifecycle detail in HR |
| Configuration | `routes/admin_settings.php`, `routes/admin_master_data.php` | `admin.settings.index` |
| Workflow & Governance | `routes/admin_governance.php`, `routes/admin.php` | `admin.governance.chains.index` |
| Integrations | `routes/admin_integrations.php` | `admin.integrations.providers.index` |
| System Operations | `routes/admin.php` | `admin.operations.audit.index` |

**Not owned here (cross-linked only):** fiscal periods, chart of accounts, HR compensation, tax codes, notification campaigns.

---

### Accounting

**Owns:** General ledger, receivables, payables, fiscal calendar, chart of accounts, posting rules, and financial period close.

| Surface | Route file | Canonical entry |
|---------|------------|-----------------|
| Workspace hub | `config/accounting_workspaces.php` | `admin.workspaces.accounting` |
| Chart of accounts | `routes/admin_accounting.php` | `admin.accounting.accounts.index` |
| Fiscal periods | `routes/admin_accounting.php` | `admin.accounting.periods.index` |
| Journals & GL | `routes/admin_accounting.php` | `admin.accounting.journals.index` |

Settings Control Center links **Chart of Accounts** and **Fiscal Years** here. Company-level currency/tax defaults remain in `settings_registry` (`company` section) under Administration.

---

### HR

**Owns:** Employee lifecycle (360 workspace), attendance, leave, payroll, compensation, performance, training, documents, and exit management.

| Surface | Route file | Canonical entry |
|---------|------------|-----------------|
| Workspace hub | `config/workspaces.php` → `hr` | `admin.workspaces.hr` |
| Employee 360 | `routes/admin_hr.php` | `admin.hr.employees.show` |
| Leave configuration | `routes/admin_hr.php` | `admin.hr.leave.config` |
| Compensation center | `routes/admin_hr.php` | `admin.hr.compensation.dashboard` |
| Payroll | `routes/admin_hr.php` | `admin.hr.payroll.dashboard` |

**Split with Administration:** `admin.employees.index` under Administration → Organization is the **master employee registry** (create/edit/list). Employee show redirects to HR Employee 360 for lifecycle management.

---

### Communications

**Owns:** Outbound messaging (SMS, email, WhatsApp), templates, notification center, shared inbox, and communication logs.

| Surface | Route file | Canonical entry |
|---------|------------|-----------------|
| Workspace hub | `config/workspaces.php` → `communications` | `admin.workspaces.communications` |
| Notification center | `routes/admin_communications.php` | `admin.communications.notifications.index` |
| SMS / Email / WhatsApp | `routes/admin_communications_*.php` | respective dashboards |
| Templates | `routes/admin_communications.php` | `admin.communications.templates.index` |

Settings Control Center **Notifications** card links here. Integration credentials (SMTP, SMS providers) remain under Administration → Integrations.

---

### Tax

**Owns:** Tax codes, tax periods, returns, ledger, reports, and tax audit trail.

| Surface | Route file | Canonical entry |
|---------|------------|-----------------|
| Tax module | `routes/admin_tax.php` | `admin.tax.codes.index` |
| Accounting tax section | `config/accounting_workspaces.php` | cross-linked from Accounting workspace |

Domain behavioral settings are unified in `config/settings_registry.php` — including `accounting`, `procurement`, `hr`, `tax`, `communications`, and `operations` sections. Full tax compliance setup (codes, returns, ledger) still lives in the Tax module routes.

---

## Settings Control Center Cross-Links

The Settings Control Center (`admin/settings` hub view) is an Administration-owned **discovery layer**. Cards resolve to live routes across workspaces:

| Card | Owner workspace | Live route |
|------|-----------------|------------|
| Chart of Accounts | Accounting | `admin.accounting.accounts.index` |
| Fiscal Years | Accounting | `admin.accounting.periods.index` |
| Customers | Commercial / CRM | `admin.crm.customers.index` |
| Warehouses | Supply Chain | `admin.inventory.warehouses.index` |
| Inventory Categories | Supply Chain | `admin.inventory.catalogue.categories.index` |
| Units of Measure | Administration (Master Data) | `admin.master-data.index` (category: `units_of_measure`) |
| Machine Configuration | Assets | `admin.assets.machines.index` |
| Approval Chains | Administration | `admin.governance.chains.index` |
| Notifications | Communications | `admin.communications.notifications.index` |
| Integrations | Administration | `admin.integrations.providers.index` |
| API Settings | Administration | `admin.integrations.api-keys.index` |
| Audit Settings | Administration | `admin.operations.retention.index` |

Cards still marked **Pending Setup** (no live admin UI yet): Cost Centers, Purchase Rules, Vendor Approvals, Vendor Evaluation.

---

## Decision Rules for New Features

1. **Identity, RBAC, org structure, integrations, audit/retention** → Administration workspace.
2. **GL, periods, COA, journals, AR/AP** → Accounting workspace.
3. **People lifecycle, payroll, leave, compensation** → HR workspace.
4. **Campaigns, templates, notification preferences** → Communications workspace.
5. **Tax codes, returns, compliance reporting** → Tax module (`admin_tax.php`).
6. **Operational module settings** (quotation rules, production workflow) → `settings_registry.php` sections, surfaced via Settings Control Center.
7. When a feature spans workspaces, add a **cross-link card** in Settings Control Center rather than duplicating CRUD in Administration.

---

## Related Files

- `config/workspaces.php` — sidebar workspace catalog
- `config/administration_workspaces.php` — Administration section hubs
- `config/settings_control_center.php` — cross-workspace settings cards
- `config/settings_registry.php` — behavioral settings sections
- `routes/admin.php` — core admin + workspace hub routes
- `app/Http/Middleware/EnsureAdminAuthContext.php` — client-session guard
