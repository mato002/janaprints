# Phase GOV-1 — Approval Chains Engine

## Readiness Table

| Area | Detail |
|------|--------|
| **Module** | Workflow & Governance → Approval Chains |
| **Tables Affected** | `approval_chains`, `approval_chain_steps`, `approval_chain_runs`, `approval_chain_step_records` (new). Reads `approval_rules` (existing — not duplicated). |
| **Routes Affected** | `admin.governance.chains.*` (index, create, store, edit, update, activate, deactivate). Administration workspace tile activated. |
| **Services Needed** | `ApprovalChainsManager`, `ApprovalChainsService`, `ApprovalChainEngine`; extends `ApprovalRulesService::evaluate()` with chain resolution. |
| **Permissions Needed** | `governance.chains.view`, `governance.chains.create`, `governance.chains.edit`, `governance.chains.activate` |
| **Indexes Needed** | `(company_id, branch_id, approval_rule_type, status)`, `(approval_chain_id, step_number)`, `(company_id, status, created_at)` on runs, `(approval_chain_run_id, status)` on step records |
| **Risks** | Chain/rule misalignment if rule disabled but chain active; branch vs company scope inheritance; parallel mode partial approvals; conditional step filtering edge cases |
| **Tests Required** | Single-step, multi-step sequential, parallel, conditional resolution, permission enforcement, workspace link, monitor statuses |

## Architecture

- **Approval Rules** (existing): determine **when** approval is required (thresholds).
- **Approval Chains** (new): determine **who** approves and **in what order** once a rule triggers.
- Chains link to `approval_rule_type` — they extend rules, never replace threshold configuration.
