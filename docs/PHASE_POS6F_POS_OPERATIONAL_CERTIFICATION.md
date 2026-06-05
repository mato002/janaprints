# PHASE POS-6F — POS Operational Certification

## Purpose

Operational certification dashboard — **not intelligence, not reporting**.

Used by Operations Manager, Finance Manager, and Internal Auditor to verify POS control truth before sign-off.

**Route:** `admin.commercial.pos.certification.index`  
**URI:** `/admin/commercial/pos/certification`

---

## Certification Domains

| Domain | Checks |
|--------|--------|
| **Inventory Truth** | Paid sales have stock deductions; completed returns restore stock; no invalid held/draft movements |
| **Accounting Truth** | Payments, returns, and variances posted to GL |
| **Cash Truth** | Open sessions clear of blockers; reconciliations approved |
| **Returns Truth** | No pending/approved-incomplete returns; no legacy refunds |
| **Session Truth** | No held/draft in open sessions; paid sales linked to sessions |
| **Branch Compliance** | Records scoped to branch; no orphan/cross-branch leakage |

---

## Certification Score

- **Score:** `(passed domains / 6) × 100`
- **Verdict:** `PASS` when all 6 domains pass, otherwise `FAIL`

---

## Permission

`commercial.pos.certification.view` — Company Admin, Branch Manager

---

## Files

| File | Role |
|------|------|
| `app/Support/Commercial/PosCertificationService.php` | Domain checks + score |
| `app/Support/Commercial/PosCertificationScope.php` | Scope DTO |
| `app/Support/Commercial/PosCertificationScopeResolver.php` | Request scope |
| `app/Http/Controllers/Admin/Commercial/PosCertificationController.php` | Dashboard |
| `resources/views/admin/commercial/pos/certification/index.blade.php` | UI |
| `tests/Feature/Commercial/PosCertificationTest.php` | Feature tests |

```bash
php artisan test --filter=PosCertificationTest
```
