# PHASE POS-6C — POS Inventory Truth

## Mission

Every **paid** POS sale deducts stock. Returns restore stock. Held and cancelled sales never touch inventory.

---

## Rules Implemented

| Rule | Behaviour |
|------|-----------|
| Paid POS sale | Creates `inventory_movements` issue rows (`movement_type = issue`) |
| Stock deduction | Balance reduced via signed movement quantity on branch MAIN warehouse |
| Ledger entry | `inventory_movements` is the ledger (source of truth) |
| Reference type | `POS_SALE` with `reference_id = pos_sales.id` |
| Insufficient stock | Blocks paid sale unless `inventory_allow_negative_stock` is enabled |
| Held sales | No inventory movement |
| Returns | Receipt movements on return completion (`POS_RETURN` reference) |
| Cancelled sales | No inventory movement |

---

## Inventory Tables Affected

| Table | Role |
|-------|------|
| `inventory_movements` | Ledger entries for POS issues and return receipts |
| `inventory_cost_layers` | Updated on issue/receipt via costing service |
| `inventory_valuations` | Recalculated on movement |
| `inventory_reorder_alerts` | Synced after balance change |

**Note:** This codebase does not use separate `inventory_balances`, `inventory_ledger_entries`, or `inventory_transactions` tables. Balances are computed from `inventory_movements`.

---

## Services Used

| Service | Role |
|---------|------|
| `PosInventoryService` | **New** — posts paid sales, restores returns, resolves POS warehouse |
| `InventoryMovementService` | Records signed movements with costing |
| `InventoryStockService` | Balance checks; respects `inventory_allow_negative_stock` |
| `InventoryCostingService` | Issue/receipt unit cost resolution |
| `PosSaleService` | Calls `postPaidSale()` on create/pay/mark-paid when status is Paid |
| `PosReturnService` | Calls `restoreReturn()` on return completion |

---

## Warehouse Resolution

POS deductions use the first active warehouse for the branch, preferring code `MAIN` (from `InventoryFoundationSeeder`).

---

## Files Added / Changed

| File | Change |
|------|--------|
| `app/Support/Commercial/PosInventoryService.php` | **New** |
| `app/Support/Commercial/PosSaleService.php` | Inventory posting on paid sales |
| `app/Support/Commercial/PosReturnService.php` | Stock restore on completed returns |
| `app/Support/InventoryStockService.php` | `allowsNegativeStock()`, gated assertions |
| `app/Support/InventoryMovementService.php` | Pass tenant context to stock assertions |
| `tests/Feature/Commercial/PosInventoryTruthTest.php` | **New** — 5 tests |

---

## Tests

**File:** `tests/Feature/Commercial/PosInventoryTruthTest.php`

| Test | Coverage |
|------|----------|
| `test_paid_sale_reduces_stock` | Issue movement + balance drop |
| `test_return_restores_stock` | Receipt movement + balance restored |
| `test_held_sale_has_no_inventory_movement` | Hold leaves stock unchanged |
| `test_cancelled_sale_has_no_inventory_movement` | Cancel leaves stock unchanged |
| `test_paid_sale_blocked_when_insufficient_stock` | Sale rolled back, stock unchanged |

```bash
php artisan test --filter=PosInventoryTruthTest
```

---

## Inventory Truth Verification

1. Post stock receipt for an item (e.g. 50 units in MAIN warehouse).
2. Complete a POS sale with `action=pay` and linked `inventory_item_id`.
3. Confirm `inventory_movements` has `reference_type = POS_SALE`, `quantity = -N`.
4. Confirm `InventoryStockService::balance()` decreased by N.
5. Hold a sale — balance unchanged.
6. Approve a return — `reference_type = POS_RETURN`, balance increases.
