# Store Management — Implementation Audit

**Date:** 2026-06-06  
**Scope:** Store / warehouse / inventory operations within the Jana Prints ERP  
**Status:** Production-oriented module with clear gaps in advanced warehousing, governance, and UI/config alignment

---

## Executive Summary

Store management in Jana Prints is implemented as the **Store Operations** hub inside the broader **Supply Chain** workspace. There is no separate `Store` database entity — the canonical model is **`Warehouse`**, with **"store"** used as a UI label for branch-level stock locations.

The module is **substantially built**, not a stub. It covers:

- Warehouse master data and manager assignments
- Movement-ledger stock balances (receive, issue, transfer, adjust)
- Physical stock counts, cycle counts, variance, and reconciliation
- FIFO costing and inventory valuation
- Integrations with procurement, production, POS, accounting, and fixed assets

Primary gaps are in **advanced warehousing** (bins, lots, serials), **approval enforcement**, **permission catalog completeness**, **dual receiving UX clarity**, and **config/UI drift** where live features are still marked "coming soon."

---

## Terminology

| Term | Meaning in this codebase |
|------|--------------------------|
| **Warehouse** | Database model (`warehouses` table). All stock documents reference `warehouse_id`. |
| **Store** | UI/workspace label only — "Store Operations", "Store Balances", "Store Management". |
| **Inventory** | Broader module: item catalogue, movements, costing, control, and reports. |
| **Balance** | Computed as `SUM(inventory_movements.quantity)` per item/warehouse — not a separate balance table. |

**Transfers** are implemented as `StockIssue` records with `destination = transfer`, creating paired `TransferOut` and `TransferIn` movements. There is no dedicated `stock_transfers` table.

---

## Architecture Overview

```
Supply Chain Workspace
├── Catalogue          → products, categories, brands, attributes, price lists
├── Store Operations   → warehouses, balances, receipts, issues, transfers, adjustments, movements
├── Procurement        → PO receiving (separate GRN flow)
├── Inventory Control  → stock counts, cycle counts, variances, reconciliation
├── Costing            → FIFO layers, valuation snapshots
├── Assets             → capitalization queue from procurement receipts
└── Reports            → inventory, procurement, valuation, movement reports
```

### Stock truth model

| Layer | Implementation |
|-------|----------------|
| Ledger | `inventory_movements` — single source of truth |
| Balance | Derived via `InventoryStockService` (cached 30s in `PlatformCacheService`) |
| Costing | `inventory_cost_layers` (FIFO) + `inventory_valuations` (weighted average) |
| Alerts | `inventory_reorder_alerts` — auto-synced on balance change |

---

## What Is Implemented

### 1. Store Operations (core)

| Feature | Route prefix | Permission | Status |
|---------|--------------|------------|--------|
| Store dashboard | `admin/inventory/store` | `inventory.view` | ✅ Live |
| Warehouses CRUD | `admin/inventory/warehouses` | `inventory.view/create/edit/delete` | ✅ Live |
| Warehouse managers | `admin/inventory/warehouses/{id}/managers` | `inventory.edit` | ✅ Live |
| Store balances | `admin/inventory/store/balances` | `inventory.view` | ✅ Live |
| Stock receipts | `admin/inventory/receipts` | `inventory.receive` | ✅ Live |
| Stock issues | `admin/inventory/issues` | `inventory.issue` | ✅ Live |
| Transfers | `admin/inventory/transfers` | `inventory.transfer` | ✅ Live |
| Adjustments | `admin/inventory/adjustments` | `inventory.adjust` | ✅ Live |
| Movement history | `admin/inventory/movements` | `inventory.view` | ✅ Live |
| Store permissions matrix | `admin/inventory/store/permissions` | `inventory.view` | ✅ Read-only |

**Key files:**
- Routes: `routes/admin_inventory.php`
- Controllers: `app/Http/Controllers/Admin/Inventory/` (27 controllers)
- Services: `app/Support/InventoryStockService.php`, `StockReceiptService.php`, `StockIssueService.php`, `StockAdjustmentService.php`, `InventoryMovementService.php`
- Views: `resources/views/admin/inventory/store/`, `warehouses/`, `receipts/`, `issues/`, `transfers/`, `adjustments/`, `movements/`

### 2. Catalogue (item master)

Full CRUD for inventory items, categories, subcategories, brands, attributes, price lists, and item images.

| Area | Routes | Status |
|------|--------|--------|
| Products | `admin/inventory/items` | ✅ Live |
| Categories / subcategories | `admin/inventory/catalogue/categories`, `subcategories` | ✅ Live |
| Brands | `admin/inventory/catalogue/brands` | ✅ Live |
| Attributes | `admin/inventory/catalogue/attributes` | ✅ Live |
| Price lists | `admin/inventory/catalogue/price-lists` | ✅ Live |

**Seeder:** `database/seeders/InventoryFoundationSeeder.php` (UOMs, categories, default warehouse, sample items)

### 3. Inventory Control

| Feature | Workflow | Status |
|---------|----------|--------|
| Stock count | Create → worksheet → submit → approve → post | ✅ Live |
| Cycle count | Schedule by warehouse/category → generate counts | ✅ Live |
| Variance report | Derived from count lines; CSV export | ✅ Live (PDF placeholder) |
| Reconciliation | Approve → post (creates adjustment) | ✅ Live |

**Services:** `app/Support/Inventory/StockCountService.php`, `CycleCountService.php`, `InventoryVarianceService.php`, `InventoryReconciliationService.php`

### 4. Costing & Valuation

| Feature | Status |
|---------|--------|
| FIFO cost layers (on every movement) | ✅ Backend live |
| Weighted-average valuation | ✅ Backend live |
| Valuation snapshots | ✅ Live (`admin/inventory/valuation`) |
| Average Cost dedicated UI | ⚠️ Marked `coming_soon` in workspace config despite backend support |

### 5. Receiving (two paths)

#### Path A — Direct stock receipt
- **Controller:** `StockReceiptController`
- **Flow:** Create draft → add lines → post
- **Sources:** `purchase`, `return`, `adjustment` (`StockReceiptSource` enum)
- **Accounting:** Posts journal for non-purchase sources; purchase source defers to procurement GRN accounting

#### Path B — Procurement goods receipt
- **Controller:** `GoodsReceiptController` (`routes/admin_procurement.php`)
- **Flow:** From approved PO → select warehouse → receive quantities → post
- **On post:** Creates linked `StockReceipt`, posts inventory movements, updates PO `quantity_received`
- **Capital lines:** Routed to asset capitalization queue (no inventory movement)
- **Accounting:** `ProcurementGoodsReceiptPosted` event

### 6. Issuing & consumption

| Channel | Implementation | Status |
|---------|----------------|--------|
| Stock issues | `StockIssueController` — destinations: production, internal_use, damage | ✅ Live |
| Production consumption | Job card materials tab + `ProductionMaterialConsumptionController` | ✅ Live |
| POS sales | `PosInventoryService` — issues on paid sale, restores on return | ✅ Live |
| Transfers | `StoreTransferController` — dual movement, no GL posting | ✅ Live |

### 7. Integrations

| Module | Integration point | Status |
|--------|-------------------|--------|
| **Procurement** | GRN → `StockReceipt` → movements; capital goods → asset queue | ✅ Live |
| **Production** | Job card material consumption → `ProductionConsumption` movements | ✅ Live |
| **Accounting** | `InventoryAccountingPostingService` — receipts, issues, adjustments, GRN | ✅ Live |
| **POS / Commercial** | Paid sales deduct stock; returns restore (`docs/PHASE_POS6C_POS_INVENTORY_TRUTH.md`) | ✅ Live |
| **Fixed Assets** | Capital goods from GRN → `AssetCapitalizationCandidate` | ✅ Live |
| **Intelligence** | Inventory 360 report (`admin.reports.inventory360`) | ✅ Live |

### 8. Permissions & roles

| Permission group | In `permission_catalog.php` | Seeded in `RolesAndPermissionsSeeder` |
|------------------|----------------------------|---------------------------------------|
| `catalogue.*` | ✅ | ✅ |
| `inventory.view/create/edit/delete` | ✅ | ✅ |
| `inventory.receive/issue/adjust/transfer` | ✅ | ✅ |
| `inventory.valuation.view` | ✅ | ✅ |
| `inventory.count.*` | ❌ Missing | ✅ |
| `inventory.cycle.*` | ❌ Missing | ✅ |
| `inventory.variance.view` | ❌ Missing | ✅ |
| `inventory.reconcile.*` | ❌ Missing | ✅ |

**Role:** `Storekeeper` maps to inventory permissions via `config/role_catalog.php`.

### 9. Test coverage

| Test file | Coverage area |
|-----------|---------------|
| `tests/Feature/Inventory/InventoryFoundationTest.php` | Isolation, receipts, issues, transfers, adjustments, production consumption |
| `tests/Feature/Inventory/StockCountTest.php` | Full count workflow |
| `tests/Feature/Inventory/CycleCountTest.php` | Cycle count schedules |
| `tests/Feature/Inventory/InventoryReconciliationTest.php` | Reconciliation |
| `tests/Feature/Inventory/InventoryVarianceTest.php` | Variance reporting |
| `tests/Feature/Inventory/InventoryCostingTest.php` | FIFO costing |
| `tests/Feature/Inventory/InventoryValuationTest.php` | Valuation |
| `tests/Feature/Inventory/InventoryReportTest.php` | Reports |
| `tests/Feature/Procurement/ProcurementFoundationTest.php` | GRN → inventory posting |
| `tests/Feature/Commercial/PosInventoryTruthTest.php` | POS stock truth |
| `tests/Feature/Assets/AssetCapitalizationTest.php` | Capital goods from GRN |

**Gaps in test coverage:** HTTP end-to-end for transfers, warehouse CRUD UI, warehouse manager assignment, direct receipt create/post UI, store permissions page.

---

## Data Model

### Core tables

```
warehouses
  └── user_warehouse (manager assignments)

inventory_items (sku, category, brand, uom, reorder_level, standard_cost)
  └── inventory_movements (ledger)
        └── inventory_cost_layers (FIFO)
        └── inventory_valuations (weighted average)

stock_receipts → stock_receipt_items → movements (Receipt)
stock_issues → stock_issue_items → movements (Issue / TransferOut + TransferIn)
stock_adjustments → stock_adjustment_items → movements (Adjustment)

goods_receipts (procurement) → stock_receipt_id (bridge)
stock_counts → stock_count_items → inventory_reconciliations → stock_adjustment_id
cycle_count_schedules → generates stock_counts
production_material_consumptions → movements (ProductionConsumption)
inventory_reorder_alerts
```

### Key migrations

| Migration | Purpose |
|-----------|---------|
| `2026_06_09_900002` – `900010` | Core inventory schema (categories, warehouses, items, movements, receipts, issues, adjustments) |
| `2026_06_11_100001` | Procurement tables including `goods_receipts` |
| `2026_06_11_120001` | `user_warehouse` manager assignments |
| `2026_06_12_130001` | Catalogue expansion (subcategories, brands, attributes, price lists) |
| `2026_06_13_140002` | Costing tables (layers, valuations, snapshots) |
| `2026_06_17_100001` | Posted journal columns on inventory documents |
| `2026_06_18_100001` | Inventory control tables (counts, cycle counts, reconciliations) |
| `2026_06_22_100000` | Reporting indexes |

---

## Gaps

### Critical / functional gaps

| # | Gap | Evidence | Impact |
|---|-----|----------|--------|
| G1 | **No bin/location/lot/serial/batch tracking** | No models, migrations, or fields; settings card mentions bins but marks warehouses `coming_soon` | Cannot support fine-grained warehouse layout or traceability |
| G2 | **Stock adjustment approval not enforced** | `stock_adjustment_approval` in `config/approval_registry.php`; `StockAdjustmentService` posts directly with permission check only | High-value adjustments bypass configured approval tiers |
| G3 | **Permission catalog incomplete** | `inventory.count/cycle/variance/reconcile.*` seeded and used in routes/policies but absent from `config/permission_catalog.php` | Role management UI cannot assign control-module permissions |
| G4 | **Dual receiving UX confusion** | "Goods Receiving" in store ops (`admin.inventory.receipts`) vs "Goods Receipts" in procurement (`admin.procurement.receipts`) | Users may receive stock through wrong path; accounting differs by path |
| G5 | **No reorder alerts management UI** | `inventory_reorder_alerts` table + auto-sync exist; no list/resolve routes | Alerts visible on dashboards only; no actionable workflow |
| G6 | **No UOM admin UI** | `UnitOfMeasure` model seeded only; no CRUD routes | Units cannot be managed without database/seed changes |
| G7 | **Variance PDF export is placeholder** | `InventoryVarianceController::exportPdf()` → `variances/pdf-placeholder.blade.php` | PDF export non-functional |
| G8 | **Average Cost UI marked coming soon** | `supply_chain_workspaces.php` line 167; backend weighted-average exists | Users cannot access costing method that is already implemented |

### Config / UI drift

| Item | Location | Issue |
|------|----------|-------|
| Warehouses settings card | `config/settings_control_center.php` | `coming_soon: true` despite live warehouse CRUD |
| Inventory categories settings card | Same | `coming_soon: true` despite live catalogue categories |
| Units of measure settings card | Same | `coming_soon: true` despite seeded UOM model |
| Store permissions page | `admin/inventory/store/permissions` | Read-only matrix; no inline grant/revoke |
| Reports tab fallback | `InventoryReportPresenter` | Returns `type: placeholder` if `inventory_valuations` table missing |

### Architectural notes

| Note | Detail |
|------|--------|
| Transfer document type | `document_types_registry` references `stock_transfer` but DB uses `StockIssue` with transfer destination |
| Warehouse types | Listed in `master_data_registry.php` but not implemented |
| Balance at scale | `SUM(inventory_movements)` with 30s cache — may degrade under high transaction volume |

---

## Recommended Improvements

### Priority 1 — Governance & permissions (low effort, high value)

1. **Add control permissions to `permission_catalog.php`**
   - `inventory.count.view/create/edit/approve/post`
   - `inventory.cycle.view/create/edit/generate`
   - `inventory.variance.view/export`
   - `inventory.reconcile.view/approve/post`

2. **Wire stock adjustment approval**
   - Integrate `StockAdjustmentService` with `stock_adjustment_approval` from `approval_registry.php`
   - Block post above threshold until approval chain completes

3. **Fix config drift**
   - Remove `coming_soon` from warehouses, categories, and UOM settings cards OR link them to live routes
   - Enable Average Cost workspace card → `admin.inventory.valuation.index`

### Priority 2 — UX clarity (medium effort)

4. **Unify store/warehouse terminology**
   - Pick one label for end users (recommend "Store" in UI, "warehouse" in API/DB)
   - Apply consistently across breadcrumbs, forms, and reports

5. **Clarify receiving paths**
   - Rename store ops "Goods Receiving" → "Direct Receipts" or "Ad-hoc Receipts"
   - Add cross-links: PO receive page → resulting stock receipt; procurement GRN list → linked `stock_receipt_id`

6. **Reorder alerts workflow**
   - Add `admin/inventory/alerts` — list, acknowledge, resolve
   - Surface on store dashboard with drill-down

7. **Complete variance PDF export**
   - Replace `pdf-placeholder.blade.php` with real export using existing CSV query layer

### Priority 3 — Feature completeness (higher effort)

8. **UOM management**
   - CRUD under catalogue or settings (`admin/inventory/catalogue/uoms`)
   - Support conversion factors for packaging units

9. **Explicit transfer document (optional)**
   - If transfer-specific fields/workflows are needed, introduce `StockTransfer` model
   - Otherwise, document current `StockIssue`-as-transfer pattern in user guide

10. **Materialized balances (scale)**
    - Add `inventory_balances` snapshot table updated on each movement
    - Keep movements as audit ledger; use balances for reads

### Priority 4 — Testing

11. **Add missing HTTP feature tests**
    - `StoreTransferController` create/post flow
    - `WarehouseController` CRUD + manager assignment
    - `StockReceiptController` create/post UI
    - Procurement GRN → accounting journal assertion

---

## User Flow Reference

### Receive stock (procurement path — recommended for PO goods)

```
Purchase Order (approved)
  → Procurement → Receive Goods (GRN draft)
  → Post GRN
      → Creates StockReceipt (linked)
      → Posts inventory_movements (Receipt)
      → Updates PO quantity_received
      → Posts accounting journal
      → Capital lines → Asset Capitalization queue
```

### Receive stock (direct path — ad-hoc / returns)

```
Store Operations → Goods Receiving → New Receipt
  → Add lines (item, qty, cost)
  → Post
      → Posts inventory_movements (Receipt)
      → Posts accounting (except purchase source)
```

### Issue to production

```
Production Job Card → Materials tab → Consume
  OR
Store Operations → Stock Issues → New Issue (destination: production)
  → Post → inventory_movements (Issue / ProductionConsumption)
  → Accounting journal (production consumption)
```

### Transfer between stores

```
Store Operations → Transfers → New Transfer
  → Source warehouse → Destination warehouse → lines
  → Post → TransferOut + TransferIn movements (no GL)
```

### Physical count & reconciliation

```
Inventory Control → Stock Count → Create (full/partial)
  → Enter counted quantities
  → Submit → Approve → Post
      → Creates StockAdjustment (variance)
      → Creates InventoryReconciliation
```

---

## File Reference (quick index)

| Area | Path |
|------|------|
| Workspace config | `config/supply_chain_workspaces.php` |
| Permissions | `config/permission_catalog.php`, `database/seeders/RolesAndPermissionsSeeder.php` |
| Settings | `config/settings_control_center.php`, `config/settings_registry.php` |
| Forms | `config/form_registry.php` |
| Approvals | `config/approval_registry.php` |
| Routes | `routes/admin_inventory.php`, `routes/admin_procurement.php` |
| Controllers | `app/Http/Controllers/Admin/Inventory/` |
| Models | `app/Models/Inventory/` (28 models) |
| Services | `app/Support/InventoryStockService.php`, `app/Support/Inventory/` |
| Procurement GRN | `app/Support/Procurement/GoodsReceiptService.php` |
| Accounting | `app/Support/Accounting/InventoryAccountingPostingService.php` |
| POS | `app/Support/Commercial/PosInventoryService.php` |
| Views | `resources/views/admin/inventory/` (74 blade files) |
| Tests | `tests/Feature/Inventory/` |
| POS inventory doc | `docs/PHASE_POS6C_POS_INVENTORY_TRUTH.md` |

---

## Conclusion

Store management is a **mature, integrated module** suitable for day-to-day print-industry stock operations at branch/warehouse level. The movement-ledger architecture, document workflows, and cross-module integrations (procurement, production, POS, accounting, assets) are well-established and tested.

The highest-value next steps are **governance alignment** (permissions catalog, adjustment approvals), **UX clarity** (receiving paths, terminology), and **removing config drift** (coming-soon flags on live features). Advanced warehousing (bins, lots, serials) and scale optimizations (materialized balances) are longer-term enhancements.

---

*Generated from codebase audit — controllers, services, models, migrations, routes, views, permissions, tests, and integration points.*
