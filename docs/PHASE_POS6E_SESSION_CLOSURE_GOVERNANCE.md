# PHASE POS-6E — Session Closure Governance

## Rules Added

Session close is blocked when any of the following exist on the session:

| Blocker | Query |
|---------|-------|
| Held sales | `pos_sales.status = held` |
| Draft sales | `pos_sales.status = draft` |
| Pending payments | `balance_due > 0` excluding held, draft, cancelled, refunded |
| Unapproved returns | `pos_returns.status IN (pending, approved)` |

**Service:** `PosSessionService::assertSessionReadyToClose()` called from `closeSession()`.

## Session Closure Checklist (UI)

| Item | Pass condition |
|------|----------------|
| All Sales Paid | No held, draft, or pending payments |
| No Held Sales | Held count = 0 |
| No Draft Sales | Draft count = 0 |
| Returns Cleared | No pending/approved returns |
| Cash Count Completed | Entered on close form |

Shown on session **show** (open sessions) and **close** form.

## Files Changed

- `app/Support/Commercial/PosSessionService.php` — `closureGovernance()`, `assertSessionReadyToClose()`
- `app/Http/Controllers/Admin/Commercial/PosSessionController.php` — pass governance to views
- `resources/views/admin/commercial/pos/sessions/partials/closure-checklist.blade.php` — **new**
- `resources/views/admin/commercial/pos/sessions/close.blade.php` — checklist + disabled form
- `resources/views/admin/commercial/pos/sessions/show.blade.php` — checklist on open sessions

## Tests

`tests/Feature/Commercial/PosSessionClosureGovernanceTest.php`

- `test_close_blocked_with_held_sale`
- `test_close_blocked_with_draft`
- `test_close_allowed_after_resolution`
- `test_close_blocked_with_unapproved_return`
- `test_close_form_shows_checklist`

```bash
php artisan test --filter=PosSessionClosureGovernanceTest
```
