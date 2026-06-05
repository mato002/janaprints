# Phase GOV-3 — Escalation Engine

## Summary

Workflow escalation management prevents approval bottlenecks by applying configurable timeout rules to pending approval steps.

## Routes

| Route | Permission |
|-------|------------|
| `admin.governance.escalations.index` | `governance.escalations.view` |
| `admin.governance.escalations.create` | `governance.escalations.manage` |
| `admin.governance.escalations.store` | `governance.escalations.manage` |
| `admin.governance.escalations.edit` | `governance.escalations.manage` |
| `admin.governance.escalations.update` | `governance.escalations.manage` |
| `admin.governance.escalations.activate` | `governance.escalations.manage` |
| `admin.governance.escalations.deactivate` | `governance.escalations.manage` |

## Permissions

- `governance.escalations.view` — view escalation rules dashboard
- `governance.escalations.manage` — create, edit, activate, and deactivate rules

## Configuration Fields

| Field | Description |
|-------|-------------|
| Workflow | Business process (Purchase Order, Inventory Adjustment, etc.) |
| Waiting Period | Hours before escalation action triggers |
| Escalation Target | Role that receives escalated approvals |
| Escalation Method | **Reminder** (notify approver) or **Auto Escalate** (reassign to target) |

## Default Seeded Rules

| Workflow | Waiting Period | Escalation Target | Method |
|----------|----------------|-------------------|--------|
| Purchase Order | 48 Hours | Finance Director | Auto Escalate |
| Inventory Adjustment | 24 Hours | Operations Manager | Auto Escalate |

## Runtime

- Command: `php artisan governance:process-escalations`
- Scheduled: every 15 minutes
- Evaluates pending `approval_chain_step_records` against active rules
- Writes audit events to `workflow_escalation_events` and `security_audit_events`

## Tests

`tests/Feature/Admin/EscalationsGovernanceTest.php`

- Reminder dispatch on timeout
- Auto escalation routing to target role
- Timeout not processed before waiting period
- Permission enforcement

## PRODUCTION GATE ARMORED

STANDING BY FOR LEAN ENGINEERING INPUTS.
