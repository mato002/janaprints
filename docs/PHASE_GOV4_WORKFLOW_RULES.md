# PHASE GOV-4 — Workflow Rules Engine

| Field | Value |
|-------|-------|
| **Module** | Workflow & Governance → Workflow Rules |
| **Route** | `admin.governance.workflow-rules.*` |
| **Permissions** | `governance.workflow.view`, `governance.workflow.create`, `governance.workflow.manage` |

## Mission

Automate ERP actions using configurable IF condition THEN action rules.

## Triggers

- Created
- Approved
- Rejected
- Completed
- Cancelled
- Closed

## Actions

- Create Document
- Send Notification
- Send Email
- Send SMS
- Assign User
- Change Status
- Generate Task
- Generate Approval

## Examples (Seeded)

| Rule | Trigger | Action |
|------|---------|--------|
| Quotation Approved → Sales Order | Approved | Create Document (`sales_order`) |
| Job Card Completed → Notify Sales | Completed | Send Notification (Sales role) |

## Architecture

| Layer | Class |
|-------|-------|
| Registry | `config/workflow_rule_registry.php` |
| Manager | `WorkflowRulesManager` |
| Engine | `WorkflowRuleEngine` |
| Dispatcher | `WorkflowRulesService` |
| Executor | `WorkflowRuleActionExecutor` |
| UI | `WorkflowRulesController` |

## Domain Hooks

- `QuotationController` — approved, accepted, rejected
- `ArtworkRequestController` — approved / rejected
- `ProductionJobCardController` — completed

## Tests

`tests/Feature/Admin/WorkflowRulesGovernanceTest.php`

- Trigger detection
- Condition evaluation
- Action execution
- Permission enforcement
- Workspace activation (no Coming Soon)

## Production Gate

| Check | Status |
|-------|--------|
| Migration | `2026_06_28_100000_create_workflow_rules_tables` |
| Permissions seeded | Yes |
| Workspace tile active | Yes |
| Seeded demo rules | Yes |
| Feature tests | Yes |

**PRODUCTION GATE ARMORED: STANDING BY FOR LEAN ENGINEERING INPUTS.**
