# Administration Module — Implementation Audit

**Date:** 2026-06-06  
**Scope:** Full Administration workspace and ERP governance layer in Jana Prints  
**Status:** ~75–85% complete for single-company ERP; not yet a full multi-tenant SaaS admin plane

---

## Executive Summary

The **Administration** workspace is the ERP's governance and configuration backbone. It is organized into **six hubs** — Security & Access, Organization, Configuration, Workflow & Governance, Integrations, and System Operations — with registry-driven settings, Spatie RBAC, company/branch tenancy, and substantial test coverage.

This is **not a thin admin shell**. Identity management, org structure, document numbering, approval chains, workflow rules, escalations, integrations (email/SMS/API/webhooks), backups, audit logs, and data retention are largely **production-grade**.

The module falls short of a **complete ERP administration plane** in four areas:

1. **Multi-tenant RBAC** — roles are global, not company-scoped
2. **Config/UI drift** — Settings Control Center marks live features as "coming soon"
3. **Split responsibilities** — fiscal periods, COA, HR config, and notifications live in other workspaces without clear Administration unification
4. **Enterprise security gaps** — no MFA, SSO, password policy admin, or user invitation/bulk import

**Bottom line:** Suitable for a single print-industry company running all branches under one legal entity. Needs hardening for SaaS multi-tenancy and enterprise security.

---

## ERP Administration — Fit Assessment

A mature ERP administration module typically provides:

| ERP Admin Capability | Jana Prints Status | Where It Lives |
|---------------------|-------------------|----------------|
| User & identity management | ✅ Live | Administration → Security & Access |
| Role-based access control (RBAC) | ✅ Live (global roles) | Spatie Permission + permission matrix |
| Multi-company / multi-branch tenancy | ⚠️ Partial | `TenantContext`, session switching; RBAC not tenant-scoped |
| Organization structure (company, branch, dept) | ✅ Live | Administration → Organization |
| Employee master data | ✅ Live (redirects to HR 360) | Administration + HR workspace |
| System settings (company/branch scope) | ✅ Live | `settings_registry.php` + `SystemSetting` model |
| Document numbering | ✅ Live | Number series registry |
| Approval rules & chains | ✅ Live | Governance stack |
| Workflow automation | ✅ Live | Workflow rules + escalations + delegations |
| Master data management | ✅ Live | Master data center |
| Form controls & document types | ✅ Live | Form control center + document types |
| Fiscal periods & period close | ✅ Live | **Accounting workspace** (not Administration) |
| Chart of accounts setup | ✅ Live | **Accounting workspace** |
| Tax configuration | ✅ Live | **Tax routes** (`admin_tax.php`) |
| Notification preferences | ⚠️ Split | **Communications workspace**; control center says coming soon |
| Integration admin (email, SMS, API) | ✅ Live | Administration → Integrations |
| Audit trail & compliance logs | ✅ Live | Activity logs + security audit + operations audit |
| Backup & retention | ✅ Live | System Operations |
| System health & job monitoring | ✅ Live | System Operations |
| Branding & white-label | ✅ Live | Branding settings |
| MFA / SSO | ❌ Missing | — |
| Password policies | ❌ Missing | — |
| User invitation / bulk import | ❌ Missing | — |
| Tenant provisioning / onboarding | ❌ Missing | — |
| Custom fields framework | ❌ Missing | — |
| Localization / timezone per branch | ❌ Missing | — |

---

## Module Map

### Hub 1 — Security & Access

| Feature | Route | Permission | Status |
|---------|-------|------------|--------|
| Users CRUD | `admin/users` | `users.*` | ✅ Live |
| Reset password / toggle active | `admin/users/{user}` | `users.edit` | ✅ Live |
| Roles governance dashboard | `admin/access-control/roles` | `roles.view` | ✅ Live |
| Role duplicate / deactivate | `admin/roles/{role}` | `roles.create/edit/delete` | ✅ Live |
| Permission matrix | `admin/access-control/matrix` | `roles.view` | ✅ Live |
| Per-role permission editor | `admin/roles/{role}/permissions` | `roles.edit` | ✅ Live |
| User sessions (terminate, force logout) | `admin/security/sessions` | `security.sessions.*` | ✅ Live |
| Access audit (auth/authz trail) | `admin/security/audit` | `security.audit.*` | ✅ Live |
| Access Control hub | `admin/access-control` | `users.view\|roles.view` | ✅ Live |

**Tests:** `AccessControlTest`, `AccessAuditCenterTest`, `UserSessionManagementTest`, `PlatformGovernanceTest`

**Known issue:** `routes/admin.php` references `PermissionController::class` (lines 106, 123–124) **without a `use` import**. This may cause route registration failure on fresh deploys unless route cache masks it.

---

### Hub 2 — Organization

| Feature | Route | Permission | Status |
|---------|-------|------------|--------|
| Companies CRUD | `admin/companies` | `companies.manage` | ✅ Live (create/delete: Super Admin only) |
| Branches CRUD | `admin/branches` | `branches.manage` | ✅ Live |
| Departments CRUD | `admin/departments` | `departments.manage` | ✅ Live |
| Employees master | `admin/employees` | `employees.manage` | ✅ Live (show → HR 360 redirect) |
| Job titles + hierarchy | `admin/job-titles` | `organization.job_titles.*` | ✅ Live |

**Policy note:** `CompanyPolicy` restricts company **create/delete** to Super Admin. **Company Admin** does not receive `companies.manage` in `RolesAndPermissionsSeeder` — cannot manage legal entity records.

**Test gap:** No dedicated Company/Branch/Department CRUD feature tests. Job titles tested in `JobTitlesOrganizationTest`.

---

### Hub 3 — Configuration

| Feature | Route | Permission | Status |
|---------|-------|------------|--------|
| System settings (registry-driven) | `admin/settings` | `settings.view/edit` | ✅ Live |
| Settings Control Center hub | `admin/settings` (hub view) | `settings.view` | ⚠️ Partial — many cards `coming_soon` |
| Branding | `admin/settings/branding` | `settings.view` | ✅ Live |
| Number series | `admin/settings/numbering` | `settings.view` | ✅ Live |
| Form controls | `admin/settings/forms` | `settings.view` | ✅ Live |
| Document types | `admin/settings/document-types` | `configuration.document_types.*` | ✅ Live |
| Master data center | `admin/master-data` | `configuration.master_data.*` | ✅ Live |

**Settings registry sections** (`config/settings_registry.php`):

| Section | Scope | Status |
|---------|-------|--------|
| `company` | Currency, tax rate, payment terms, fiscal year start | ✅ Live |
| `branch` | Display name, phone, email | ✅ Live |
| `crm` | Lead follow-up, customer email requirement | ✅ Live |
| `quotation` | Validity, approval, max discount | ✅ Live |
| `artwork` | Customer approval, due days, auto-assign | ✅ Live |
| `sales-order` | Required days, auto job card, deposit | ✅ Live |
| `production` | Priority, auto-schedule, QC required | ✅ Live |
| `inventory` | Negative stock, reorder alerts, receipt source | ✅ Live |
| accounting | — | ❌ Not in registry |
| procurement | — | ❌ Not in registry |
| hr | — | ❌ Not in registry (HR config in HR workspace) |
| tax | — | ❌ Not in registry |

**Tests:** `SettingsGovernanceTest`, `NumberingGovernanceTest`, `FormGovernanceTest`, `DocumentTypesGovernanceTest`, `MasterDataCenterTest`

---

### Hub 4 — Workflow & Governance

| Feature | Route | Permission | Status |
|---------|-------|------------|--------|
| Approval rules (thresholds) | `admin/settings/approvals` | `settings.view` | ✅ Live |
| Approval chains | `admin/governance/chains` | `governance.chains.*` | ✅ Live |
| Workflow rules | `admin/governance/workflow-rules` | `governance.workflow.*` | ✅ Live |
| Escalations | `admin/governance/escalations` | `governance.escalations.*` | ✅ Live |
| Delegations | `admin/governance/delegations` | `governance.delegations.*` | ✅ Live |

**Registries:** `approval_registry.php`, `chain_registry.php`, `workflow_rule_registry.php`, `delegation_registry.php`

**Scheduled job:** `governance:process-escalations`

**Tests:** `ApprovalGovernanceTest`, `ApprovalChainsCenterTest`, `WorkflowRulesGovernanceTest`, `EscalationsGovernanceTest`, `ApprovalDelegationsGovernanceTest`

**Gap:** Approval registry defines rules (e.g. `stock_adjustment_approval`, `procurement_approval`) but **enforcement in business services is inconsistent** — some modules post without checking approval chains.

---

### Hub 5 — Integrations

| Feature | Route | Permission | Status |
|---------|-------|------------|--------|
| Email settings + test send | `admin/integrations/email` | `integrations.email.manage` | ✅ Live |
| SMS settings + test send | `admin/integrations/sms` | `integrations.sms.manage` | ✅ Live |
| API keys lifecycle | `admin/integrations/api-keys` | `integrations.api.manage` | ✅ Live |
| Webhooks + retry | `admin/integrations/webhooks` | `integrations.webhooks.manage` | ✅ Live |
| Third-party providers | `admin/integrations/providers` | `integrations.providers.manage` | ✅ Live |

**Tests:** `tests/Feature/Integrations/*`

**Config drift:** Settings Control Center marks Integrations and API Settings as `coming_soon: true` while live routes exist under `routes/admin_integrations.php`.

---

### Hub 6 — System Operations

| Feature | Route | Permission | Status |
|---------|-------|------------|--------|
| Compliance audit logs + export | `admin/operations/audit` | `operations.audit.*` | ✅ Live |
| Activity logs (read-only) | `admin/activity-logs` | `activity_logs.view` | ✅ Live |
| Background jobs (retry/cancel) | `admin/operations/jobs` | `operations.jobs.*` | ✅ Live |
| System health + refresh | `admin/operations/health` | `operations.health.*` | ✅ Live |
| Backups (view/download/manage) | `admin/operations/backups` | `operations.backups.*` | ✅ Live |
| Data retention policies | `admin/operations/retention` | `operations.retention.*` | ✅ Live |

**Tests:** `AuditLogsCenterTest`, `BackgroundJobsCenterTest`, `SystemHealthCenterTest`, `BackupManagementCenterTest`, `DataRetentionCenterTest`

**Gap:** Backup **viewing** exists; scheduled backup configuration UI is unclear. Audit settings card in control center is `coming_soon` despite retention backend existing.

---

## Architecture

### Request flow

```
Staff login (/admin/login)
  → auth + verified
  → EnsureAdminAuthContext (blocks client-portal session context)
  → SetTenantContext (company + branch in session)
  → Spatie permission middleware
  → Policy checks in controllers
```

### Tenancy model

| Concept | Implementation |
|---------|----------------|
| Company scope | `users.company_id`, `TenantContext::companyId()` |
| Branch scope | `users.default_branch_id`, session `active_branch_id` |
| Super Admin | Can switch company via `POST admin/context` |
| Data isolation | `ScopesToTenant` trait on org/settings controllers |
| Query filtering | Company ID on most business tables |

**Limitation:** Roles and permissions are **global** (`config/permission.php` → `'teams' => false`). A "Branch Manager" role in Company A is the same role object as in Company B.

### RBAC (Spatie Permission)

| Item | Detail |
|------|--------|
| Package | `spatie/laravel-permission` |
| Seeded roles | 10: Super Admin, Company Admin, Branch Manager, Sales, Designer, Production, Storekeeper, Accountant, HR, Viewer |
| Permission catalog UI | `config/permission_catalog.php` — administration module entities |
| Role taxonomy | `config/role_catalog.php` |
| Route protection | `permission:*` middleware |
| Policies | `UserPolicy`, `RolePolicy`, `CompanyPolicy`, `BranchPolicy`, `DepartmentPolicy`, `SettingsPolicy`, `MasterDataPolicy`, etc. |

**User creation limitation:** `UserController::store` assigns a **single role** via `syncRoles([$data['role']])` — no multi-role assignment in the create form.

### Settings persistence

```
config/settings_registry.php  →  defines keys, types, scopes
SystemSetting model           →  company_id, branch_id, key, value (JSON)
PlatformConfigurationSeeder   →  seeds defaults
SettingsControlCenterPresenter →  navigation hub
```

Company and branch settings support **inheritance**: branch can override company defaults.

### Audit trail (two layers)

| Layer | Model | Purpose |
|-------|-------|---------|
| Activity logs | `ActivityLog` | User CRUD and business actions |
| Security audit | `SecurityAuditEvent` | Authentication and authorization events |
| Operations audit | `SecurityAuditEvent` (separate views) | Compliance-grade system audit |

Retention defaults: `config/platform.php` → `retention.defaults`

### Middleware inconsistency

| Route file | Middleware stack |
|------------|------------------|
| `routes/admin.php` | `auth`, **`admin.auth`**, `verified`, `tenant` |
| `routes/admin_settings.php` | `auth`, `verified`, `tenant` — **no `admin.auth`** |
| `routes/admin_governance.php` | `auth`, `verified`, `tenant` — **no `admin.auth`** |
| `routes/admin_integrations.php` | `auth`, `verified`, `tenant` — **no `admin.auth`** |

Client-portal accounts are unlikely to hold admin permissions, but omitting `admin.auth` is a **defense-in-depth gap**.

---

## What Works Well

1. **Mature six-hub workspace** — Permission-gated navigation with search; tested in `AdministrationWorkspaceRestructureTest`.

2. **Deep governance stack** — Approval rules, chains, workflow rules, escalations, and delegations all have controllers, registries, migrations, and dedicated tests. This exceeds typical ERP admin depth.

3. **Operations center is real** — Backups, health monitoring, job queue management, retention policies, and audit export are implemented with services — not placeholders.

4. **Integrations admin is substantial** — Email/SMS with test sends, API key lifecycle, webhooks with retry, third-party provider catalog.

5. **Registry-driven configuration** — Settings, numbering, forms, document types, master data, and approvals all use config registries — extensible without controller changes.

6. **Tenant isolation on users** — `PlatformGovernanceTest` verifies cross-company user edit is forbidden.

7. **Role governance UX** — Role dashboard with insights, deactivation registry, duplicate role, Super Admin protection in `RolePolicy`.

8. **Strong governance test surface** — 20+ feature tests under `tests/Feature/Admin/` for administration concerns.

---

## Weaknesses & Gaps

### P0 — Bugs & security hardening

| # | Gap | Evidence | Impact |
|---|-----|----------|--------|
| A1 | **Missing `PermissionController` import** | `routes/admin.php` lines 106, 123–124 | Route registration failure on permission matrix |
| A2 | **`admin.auth` missing on settings/governance/integrations routes** | Compare `admin.php` vs `admin_settings.php` | Client-context sessions could reach settings routes |
| A3 | **Global RBAC, not tenant-scoped** | `config/permission.php` → `'teams' => false` | Multi-tenant SaaS cannot isolate roles per company |

### P1 — Functional gaps

| # | Gap | Evidence | Impact |
|---|-----|----------|--------|
| A4 | **Company Admin cannot manage legal entity** | `CompanyPolicy` + seeder omits `companies.manage` for Company Admin | Tenant admin cannot edit own company profile |
| A5 | **Settings Control Center drift** | 14 cards marked `coming_soon` including integrations, API, notifications, audit — features that exist | Admin confusion; poor discoverability |
| A6 | **Settings registry incomplete** | No accounting, procurement, HR, tax sections | ERP behavior split across workspaces without unified settings |
| A7 | **Employees duplicated across workspaces** | Administration org + HR Employee 360; show redirects to HR | Unclear which entry point is canonical |
| A8 | **Single role per user on create** | `UserController::store` → `syncRoles([one role])` | Cannot assign combined roles at onboarding |
| A9 | **No branch-level RBAC** | Permissions global; branch only filters data via tenant context | Branch managers may see permissions they shouldn't |
| A10 | **Approval rules not enforced everywhere** | Registry exists; business services post without approval check in some modules | Governance config is decorative in places |

### P2 — Missing ERP admin features

| # | Gap | Typical ERP expectation |
|---|-----|------------------------|
| A11 | No MFA / 2FA | Enterprise security baseline |
| A12 | No SSO (SAML/OAuth) | Enterprise identity federation |
| A13 | No password policy admin | Minimum length, expiry, complexity rules |
| A14 | No user invitation flow | Email invite with token-based setup |
| A15 | No bulk user import | CSV/Excel onboarding |
| A16 | No tenant provisioning wizard | SaaS onboarding for new companies |
| A17 | No custom fields framework | Extensible metadata on org entities |
| A18 | No localization/timezone admin | Per-branch locale and timezone |
| A19 | No license/subscription management | SaaS billing integration |
| A20 | No API usage analytics | Developer portal metrics |

### P3 — Config/UI drift (coming soon vs live)

| Control Center card | Reality |
|--------------------|---------|
| Notifications | Live in Communications workspace |
| Audit Settings | Retention backend live in System Operations |
| Integrations | Live at `admin.integrations.*` |
| API Settings | Live at `admin.integrations.api-keys` |
| Warehouses | Live in Supply Chain → Store Operations |
| Inventory Categories | Live in Supply Chain → Catalogue |
| Units of Measure | Seeded model; no admin UI |
| Cost centers | Not implemented |
| Machine configuration | Live in Assets workspace |
| Customers settings | Live in CRM workspace |
| Procurement approval cards | Not implemented as dedicated admin screens |

### P4 — Testing gaps

| Area | Status |
|------|--------|
| Company/Branch/Department CRUD | ❌ No feature tests |
| User management full CRUD | ⚠️ Partial (`PlatformGovernanceTest` only) |
| Permission matrix routes | ⚠️ May be broken (missing import) |
| Tenant context switching | ⚠️ Limited coverage |
| Settings inheritance (company → branch) | ✅ `SettingsGovernanceTest` |

---

## Admin-Adjacent Features (Other Workspaces)

These are ERP administration concerns that live **outside** the Administration hub:

| Feature | Workspace | Route | Notes |
|---------|-----------|-------|-------|
| Fiscal years / period close | Accounting | `admin.accounting.periods.*` | Cross-linked from settings control center |
| Chart of accounts | Accounting | `admin.accounting.accounts.*` | Seeded via `JanaPrintsChartOfAccountsSeeder` |
| Posting rules | Accounting | `admin.accounting.posting.*` | GL automation |
| Tax setup | Tax module | `admin.tax.*` | Separate route file |
| Leave configuration | HR | `admin.hr.leave.config` | New HR admin surface |
| Compensation center | HR | `admin.hr.compensation.*` | In progress |
| Notification templates | Communications | `admin.communications.*` | Email/SMS templates, inbox |
| Notification preferences | Communications | Communications settings | Control center says coming soon |

**ERP fit recommendation:** Either expand Administration to be the **single settings entry point** with cross-links, or document the workspace split clearly in admin onboarding.

---

## What Still Needs Building

### For single-company ERP completeness

| Item | Priority | Effort |
|------|----------|--------|
| Fix `PermissionController` import + `admin.auth` on all admin routes | P0 | Low |
| Reconcile Settings Control Center cards with live routes | P0 | Low |
| Add Company/Branch/Department feature tests | P1 | Medium |
| Extend `settings_registry` for accounting, procurement, HR, tax | P1 | Medium |
| Company Admin company-profile edit (without Super Admin) | P1 | Low |
| Multi-role assignment on user create/edit | P1 | Low |
| Unified notification admin entry (link Communications → Administration) | P1 | Low |
| Audit settings UI (wire to existing retention backend) | P2 | Medium |
| User invitation flow | P2 | Medium |
| Password policy configuration | P2 | Medium |

### For multi-tenant SaaS completeness

| Item | Priority | Effort |
|------|----------|--------|
| Spatie teams or custom company-scoped roles | P1 | High |
| Tenant provisioning / onboarding wizard | P2 | High |
| Per-tenant custom roles | P2 | High |
| Branch-scoped permissions | P2 | High |
| Tenant suspend/delete lifecycle | P3 | High |
| License/billing admin | P3 | High |

### For enterprise security

| Item | Priority | Effort |
|------|----------|--------|
| MFA / TOTP | P2 | Medium |
| SSO (SAML/OAuth) | P3 | High |
| IP allowlisting | P3 | Medium |
| Session timeout configuration | P2 | Low |
| Login rate-limit admin | P3 | Low |

---

## Improvement Roadmap

### Phase 1 — Fix & align (1–2 weeks)

1. Add `use App\Http\Controllers\Admin\PermissionController;` to `routes/admin.php`
2. Add `admin.auth` middleware to `admin_settings.php`, `admin_governance.php`, `admin_integrations.php`, `admin_master_data.php`
3. Update Settings Control Center — link integrations, API, audit, notifications cards to live routes
4. Remove `coming_soon` from cards where features exist (warehouses, categories, machine config)
5. Document Employees entry: Administration = master CRUD, HR = Employee 360 lifecycle

### Phase 2 — ERP admin completeness (2–4 weeks)

6. Extend `settings_registry.php` with `accounting`, `procurement`, `hr`, `tax` sections
7. Grant Company Admin `companies.manage` for own company edit (policy-scoped, not create/delete)
8. Multi-role assignment in user create/edit forms
9. Company/Branch/Department HTTP feature tests
10. Wire approval registry enforcement in inventory, procurement, and finance services
11. User invitation flow (email token → password set)

### Phase 3 — Enterprise & SaaS (1–3 months)

12. Evaluate Spatie teams for tenant-scoped RBAC
13. MFA enrollment and enforcement
14. Password policy admin (length, complexity, expiry)
15. Tenant provisioning wizard for Super Admin
16. Custom fields framework for org entities
17. SSO integration admin
18. API usage analytics dashboard

---

## Seeded Roles & Permission Coverage

| Role | Administration coverage |
|------|------------------------|
| **Super Admin** | Full — including `companies.manage`, all operations, all governance |
| **Company Admin** | Users, roles (no delete), branches, departments, employees, job titles, settings, governance, operations, integrations — **no `companies.manage`** |
| **Branch Manager** | Subset of company admin scoped by branch context |
| **Viewer** | Read-only across assigned modules |
| **Storekeeper** | Inventory permissions via `role_catalog.php` |
| **Accountant** | Accounting + limited admin |
| **HR** | HR module + employee management |

---

## User Flow Reference

### Onboard a new staff user

```
Administration → Security & Access → Users → Create
  → Name, email, company, default branch, single role
  → User receives credentials (no invitation flow)
  → User logs in at /admin/login
  → TenantContext sets company/branch from user defaults
```

### Configure approval for high-value quotations

```
Administration → Workflow & Governance → Approval Rules
  → Set quotation discount threshold tiers
  → Administration → Approval Chains
  → Define multi-step sign-off path
  → (Business module must check chain on submit — verify per module)
```

### Switch company context (Super Admin)

```
POST admin/context { company_id, branch_id }
  → Session updated
  → All subsequent queries scoped to new tenant
```

### Configure document numbering

```
Administration → Configuration → Number Series
  → Select document type from numbering_registry
  → Set prefix, padding, next sequence
  → Applied on next document creation
```

---

## File Reference

### Config & navigation

| File | Purpose |
|------|---------|
| `config/workspaces.php` | Top-level workspace definitions |
| `config/administration_workspaces.php` | Six administration hubs + section items |
| `config/navigation.php` | Sidebar navigation |
| `config/permission_catalog.php` | Permission matrix UI taxonomy |
| `config/role_catalog.php` | Role governance UI taxonomy |
| `config/settings_registry.php` | Settings keys and scopes |
| `config/settings_control_center.php` | Settings hub card navigation |
| `config/approval_registry.php` | Approval rule definitions |
| `config/numbering_registry.php` | Document numbering profiles |
| `config/chain_registry.php` | Approval chain templates |
| `config/workflow_rule_registry.php` | Workflow automation rules |
| `config/delegation_registry.php` | Approval delegation rules |
| `config/master_data_registry.php` | Master data categories |
| `config/form_control_center.php` | Form field visibility/required rules |
| `config/platform.php` | Cache TTLs, retention defaults, backups |
| `config/permission.php` | Spatie permission config (`teams => false`) |

### Routes

| File | Scope |
|------|-------|
| `routes/admin.php` | Users, roles, org, operations, security, workspace hub |
| `routes/admin_settings.php` | System settings, branding, numbering, forms, approvals |
| `routes/admin_governance.php` | Chains, workflow rules, escalations, delegations |
| `routes/admin_integrations.php` | Email, SMS, API keys, webhooks, providers |
| `routes/admin_master_data.php` | Master data center |

### Middleware & tenancy

| File | Purpose |
|------|---------|
| `app/Http/Middleware/EnsureAdminAuthContext.php` | Blocks client-portal sessions from admin |
| `app/Http/Middleware/SetTenantContext.php` | Resolves company/branch context |
| `app/Support/TenantContext.php` | Tenant singleton |
| `app/Http/Controllers/Admin/TenantContextController.php` | Company/branch switcher |

### Controllers (Administration core)

| Area | Path |
|------|------|
| Workspace hub | `app/Http/Controllers/Admin/Administration/AdministrationWorkspaceController.php` |
| Users | `app/Http/Controllers/Admin/UserController.php` |
| Roles | `app/Http/Controllers/Admin/RoleController.php` |
| Permissions | `app/Http/Controllers/Admin/PermissionController.php` |
| Access control | `app/Http/Controllers/Admin/AccessControlController.php` |
| Organization | `app/Http/Controllers/Admin/CompanyController.php`, `BranchController.php`, `DepartmentController.php`, `EmployeeController.php`, `JobTitleController.php` |
| Settings | `app/Http/Controllers/Admin/SettingsController.php` + `*SettingsController.php` |
| Governance | `app/Http/Controllers/Admin/Governance/` |
| Operations | `app/Http/Controllers/Admin/Operations/` |
| Integrations | `app/Http/Controllers/Admin/Integrations/` |
| Master data | `app/Http/Controllers/Admin/MasterDataController.php` |
| Security | `app/Http/Controllers/Admin/UserSessionController.php`, `AccessAuditController.php` |

### Models

| Model | Purpose |
|-------|---------|
| `app/Models/User.php` | Staff accounts (company_id, employee_id, customer_id) |
| `app/Models/Company.php` | Legal entities |
| `app/Models/Branch.php` | Branch locations |
| `app/Models/Department.php` | Org units |
| `app/Models/Platform/SystemSetting.php` | Scoped settings persistence |
| `app/Models/MasterDataValue.php` | Master data entries |
| `app/Models/ActivityLog.php` | User activity trail |
| `app/Models/SecurityAuditEvent.php` | Security/compliance audit |
| `app/Models/UserSessionRecord.php` | Active session tracking |

### Views

| Area | Path |
|------|------|
| Administration hub | `resources/views/admin/administration/workspaces/` |
| Users | `resources/views/admin/users/` |
| Roles | `resources/views/admin/roles/` |
| Access control | `resources/views/admin/access-control/` |
| Organization | `resources/views/admin/companies/`, `branches/`, `departments/`, `employees/`, `job-titles/` |
| Settings | `resources/views/admin/settings/` |
| Governance | `resources/views/admin/governance/` |
| Integrations | `resources/views/admin/integrations/` |
| Operations | `resources/views/admin/operations/` |
| Security | `resources/views/admin/security/` |
| Master data | `resources/views/admin/master-data/` |

### Seeders

| Seeder | Purpose |
|--------|---------|
| `database/seeders/RolesAndPermissionsSeeder.php` | 10 roles + full permission set |
| `database/seeders/OrganizationFoundationSeeder.php` | JANA company, HQ branch, departments |
| `database/seeders/PlatformConfigurationSeeder.php` | Default system settings |

### Tests

| Path | Coverage |
|------|----------|
| `tests/Feature/Administration/AdministrationWorkspaceRestructureTest.php` | Workspace navigation |
| `tests/Feature/Admin/AccessControlTest.php` | RBAC basics |
| `tests/Feature/Admin/PlatformGovernanceTest.php` | Tenant isolation |
| `tests/Feature/Admin/SettingsGovernanceTest.php` | Settings scoping |
| `tests/Feature/Admin/*GovernanceTest.php` | Approval, chains, workflow, escalations, delegations |
| `tests/Feature/Admin/*CenterTest.php` | Operations centers |
| `tests/Feature/Integrations/*` | Integration admin |
| `tests/Feature/Auth/ClientAuthenticationTest.php` | Client vs staff auth separation |

---

## Conclusion

The Administration module is the **strongest governance layer** in the Jana Prints ERP. Identity, org structure, settings, workflow automation, integrations, and system operations are largely production-ready with meaningful test coverage.

To fully **fit the ERP way**, the module needs:

1. **Internal consistency** — fix route bugs, align control center with live features, unify settings registry
2. **Clear workspace boundaries** — document or consolidate admin functions currently split across Accounting, HR, Communications, and Tax
3. **Governance enforcement** — approval registry must be wired in all business services, not just configured
4. **Multi-tenant readiness** — tenant-scoped RBAC, company admin self-service, tenant provisioning
5. **Enterprise security** — MFA, password policies, user invitations

For a **single-company print business**, the module is usable today with Phase 1 fixes. For **SaaS or enterprise deployment**, Phases 2–3 are required.

---

*Generated from codebase audit — routes, controllers, services, models, migrations, views, permissions, policies, seeders, tests, and config registries.*
