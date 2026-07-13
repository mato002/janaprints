# PHASE POS-6D — POS Accounting Truth

## Mission

Every **paid** POS transaction posts to the General Ledger. Returns reverse revenue. Cash variances post on reconciliation approval.

---

## Posting Events

| Event | Code | Trigger |
|-------|------|---------|
| `POS_SALE_CASH` | `pos.sale.cash` | Paid sale — cash payment line |
| `POS_SALE_MPESA` | `pos.sale.mpesa` | Paid sale — M-Pesa payment line |
| `POS_SALE_CARD` | `pos.sale.card` | Paid sale — card payment line |
| `POS_SALE_BANK` | `pos.sale.bank` | Paid sale — bank payment line |
| `POS_RETURN` | `pos.return` | Return completed with refund |
| `POS_VARIANCE` | `pos.variance` | Cash reconciliation approved with variance |

---

## Journal Examples

### Cash Sale (KES 500)

| Account | Code | Debit | Credit |
|---------|------|------:|-------:|
| Cash Till | 1120 | 500 | |
| Retail Revenue | 4110 | | 500 |

### M-Pesa Sale (KES 750)

| Account | Code | Debit | Credit |
|---------|------|------:|-------:|
| M-Pesa Clearing | 1210 | 750 | |
| Retail Revenue | 4110 | | 750 |

### Card Sale (KES 1,200)

| Account | Code | Debit | Credit |
|---------|------|------:|-------:|
| Card Clearing | 1240 | 1,200 | |
| Retail Revenue | 4110 | | 1,200 |

### Return Reversal (KES 400 cash refund)

| Account | Code | Debit | Credit |
|---------|------|------:|-------:|
| Sales Returns | 4160 | 400 | |
| Cash Till | 1120 | | 400 |

### Cash Short Variance (KES 50)

| Account | Code | Debit | Credit |
|---------|------|------:|-------:|
| Cash Shortage | 6910 | 50 | |
| Cash Till | 1120 | | 50 |

---

## Rules

| Rule | Implementation |
|------|----------------|
| Only paid sales post | `PosAccountingPostingService::postPaidSale()` on Paid status only |
| Held sales | No GL posting |
| Cancelled sales | No GL posting |
| Returns reverse sale | DR Sales Returns, CR refund clearing account |
| Idempotency | One journal per `pos_payment` / `pos_return` / reconciliation |
| Bank POS payments | Posted via `pos.sale.bank` |

---

## Posting Rules & Templates Added

**Migration:** `2026_06_25_100001_seed_pos_posting_templates.php`

| Template | Lines |
|----------|-------|
| `pos_sale_cash` | DR `cash_till` · CR `retail_revenue` |
| `pos_sale_mpesa` | DR `mpesa_clearing` · CR `retail_revenue` |
| `pos_sale_card` | DR `card_clearing` · CR `retail_revenue` |
| `pos_return` | DR `sales_returns` · CR `refund_account` (context) |
| `pos_variance` | Short/over split via `short_amount` / `over_amount` context fields |

**Account keys** (`config/posting_account_keys.php`): `cash_till`, `card_clearing`, `retail_revenue`, `sales_returns`, `cash_shortage_expense`, `cash_overage_income`

**COA additions** (`config/jana_prints_chart_of_accounts.php`): `1240` Card Clearing, `4160` Sales Returns, `4170` Cash Overage, `6910` Cash Shortage

---

## Services Used

| Service | Role |
|---------|------|
| `PosAccountingPostingService` | **New** — posts sales, returns, variances |
| `AccountingPostingService` | Event → template → journal pipeline |
| `PostingTemplateBuilderService` | Resolves template lines |
| `JournalPostingService` | Creates and posts journals |
| `PosSaleService` | Calls accounting on paid sales |
| `PosReturnService` | Calls accounting on return completion |
| `PosCashReconciliationService` | Calls accounting on approval |

---

## Files Added / Changed

| File | Change |
|------|--------|
| `app/Enums/PostingEventCode.php` | 5 POS events |
| `app/Enums/PostingModule.php` | `Pos` module |
| `app/Support/Accounting/PosAccountingPostingService.php` | **New** |
| `config/posting_account_keys.php` | POS account keys |
| `config/jana_prints_chart_of_accounts.php` | New GL accounts |
| `database/migrations/2026_06_25_100000_add_pos_posted_journal_columns.php` | **New** |
| `database/migrations/2026_06_25_100001_seed_pos_posting_templates.php` | **New** |
| `tests/Feature/Commercial/PosAccountingTruthTest.php` | **New** — 6 tests |

---

## Tests

```bash
php artisan test --filter=PosAccountingTruthTest
```

| Test | Coverage |
|------|----------|
| `test_cash_sale_journal` | DR Cash Till, CR Retail Revenue |
| `test_mpesa_sale_journal` | DR M-Pesa Clearing |
| `test_card_sale_journal` | DR Card Clearing |
| `test_return_reversal_journal` | DR Sales Returns, CR Cash Till |
| `test_no_duplicate_posting` | Idempotent per payment |
| `test_held_sale_does_not_post_journal` | No journal on hold |
