# Public Hash Route IDs

Jana Prints ERP uses opaque **public hash** tokens in URLs instead of raw numeric database IDs.

**Status: PUBLIC HASH SECURITY CERTIFIED** (PUBLIC-HASH-06)

## Why

Numeric IDs in routes such as `/admin/crm/customers/32` are sequential and enumerable. Authentication and policies remain mandatory, but opaque route identifiers reduce probing surface and improve defense in depth.

## Format rules

| Rule | Value |
|------|-------|
| Length | 16 characters |
| Charset | Base62 (`0-9`, `A-Z`, `a-z`) |
| Column | `public_id` |
| Prefixes | **Forbidden** — plain hash only |

**Correct:** `8jkP2Ld93QmT6RwX`

**Wrong:** `INV_8jkP2Ld93QmT6RwX`, `QT_8jkP2Ld93QmT6RwX`, `JOB_8jkP2Ld93QmT6RwX`

## Certified models (Tier 1 + Tier 2)

All models in `config/public_hashes.php` → `route_exposed_models`:

| Tier | Models |
|------|--------|
| **Tier 1** | Customer, Lead, Quotation, SalesOrder, ProductionJobCard, CustomerInvoice, CustomerPayment |
| **Tier 2** | ProductionSpecification, PrintProductTemplate, ProductionQueue, WorkCenter, QualityCheck, ArtworkRequest, ArtworkFile, ArtworkVersion, DeliveryNote, InventoryItem, Warehouse, StockReceipt, StockIssue, StockAdjustment, FixedAsset, MaintenanceWorkOrder |

## Deferred models (not on public hash yet)

Documented in `config/public_hashes.php` → `deferred_models`:

| Model | Reason |
|-------|--------|
| `CustomerArtwork` | Client artwork library routes |
| `PayrollPayslip` | ESS payslip download |
| `EmployeeDocument` | ESS document download |
| `ErpNotification` | Admin notification bind |
| `SmsCampaign` | SMS campaign routes |
| `CommunicationConversation` | Inbox conversation routes |

Procurement, accounting, and HR admin models remain out of scope until a future phase.

## Components

| Component | Location |
|-----------|----------|
| Configuration | `config/public_hashes.php` |
| Generator | `app/Support/PublicHash/PublicHashGenerator.php` |
| Resolver | `app/Support/PublicHash/PublicHashResolver.php` |
| Certification | `app/Support/PublicHash/PublicHashCertificationService.php` |
| Model trait | `app/Models/Concerns/HasPublicHash.php` |
| Backfill | `php artisan public-hash:backfill` |
| Audit | `php artisan public-hash:audit` |
| Certify | `php artisan public-hash:certify` |
| Stored URL audit | `php artisan public-hash:audit-stored-urls` |
| Stored URL rewrite | `php artisan public-hash:rewrite-stored-urls` (dry-run default) |
| Migration helper | `database/migrations/helpers/add_public_id_column.php` |

## Commands

### Certification (CI gate)

```bash
composer public-hash:check
# or individually:
php artisan public-hash:certify --strict
php artisan public-hash:audit --strict --routes --views --js
php artisan public-hash:audit-stored-urls --strict
```

`public-hash:certify` checks:

- All configured models have `public_id` column and zero null rows
- All use `HasPublicHash` and `getRouteKeyName() === public_id`
- All existing hashes are valid base62
- No duplicate `public_id` per table
- `numeric_fallback_enabled` is **false**
- Deferred model list is documented

Options: `--strict`, `--json`

### Backfill

```bash
php artisan public-hash:backfill --model="App\Models\Crm\Customer"
php artisan public-hash:backfill --all --dry-run
```

### Audit

```bash
php artisan public-hash:audit --strict --routes --views --js
```

Deferred route bindings and views are allowlisted in config (communications, operations-advisor).

### Stored URL rewrite (optional)

```bash
php artisan public-hash:rewrite-stored-urls          # dry-run
php artisan public-hash:rewrite-stored-urls --apply  # persist
```

Rewrites known Tier 1/2 notification `action_url` patterns only. Does not touch signed URLs.

## Numeric fallback sunset (PUBLIC-HASH-06)

| Setting | Default | Env |
|---------|---------|-----|
| `numeric_fallback_enabled` | **false** | `PUBLIC_HASH_NUMERIC_FALLBACK` |
| `signed_receipt_legacy_numeric_enabled` | **true** | `PUBLIC_HASH_SIGNED_RECEIPT_LEGACY_NUMERIC` |

**After sunset:**

| URL type | Behavior |
|----------|----------|
| `/admin/crm/customers/32` | **404** |
| `/client/orders/15` | **404** |
| `/admin/crm/customers/8jkP2Ld93QmT6RwX` | Works |
| Signed `/payment-receipt/{hash}?signature=...` | Works |
| Signed `/payment-receipt/{numeric_id}?signature=...` | Works during 30-day TTL (route-specific exception) |
| Unsigned `/payment-receipt/...` | **403** |

Fallback usage (when enabled in local/testing) logs to `public_hash.numeric_fallback` with route, model, user, client, IP, and timestamp.

## NOT NULL enforcement

Migration `2026_07_06_200001_enforce_public_id_not_null_on_certified_tables.php` enforces NOT NULL on MySQL/MariaDB/PostgreSQL after backfill.

SQLite (test harness) skips schema alteration; application layer still assigns `public_id` on create via `HasPublicHash`.

## Developer guide — correct patterns

### Route links

```blade
{{-- Correct: pass the model --}}
<a href="{{ route('admin.crm.customers.show', $customer) }}">

{{-- Wrong: numeric ID --}}
<a href="{{ route('admin.crm.customers.show', $customer->id) }}">
```

### Data attributes for JS

```blade
{{-- Correct: full hash URL --}}
<button data-url="{{ route('admin.production.job-cards.show', $jobCard) }}">

{{-- Wrong: numeric ID used to build URL in JS --}}
<button data-id="{{ $jobCard->id }}">
```

### Eager loads

When using partial selects on hash models, include `public_id`:

```php
->with('jobCard:id,public_id,job_card_number,status')
```

### Custom `Route::bind()`

```php
Route::bind('artwork', function (string $value) {
    return app(\App\Support\PublicHash\PublicHashResolver::class)
        ->resolve(\App\Models\Artwork\ArtworkRequest::class, $value);
});
```

## Opting in a new model

1. Add migration with `add_public_id_column()`
2. Run `public-hash:backfill --model=...`
3. Add `HasPublicHash` trait
4. Register in `route_exposed_models`
5. Replace `->id` route params in views/services/JS
6. Run `composer public-hash:check`
7. Add NOT NULL migration when backfill is complete

## Testing

| Suite | Path |
|-------|------|
| Foundation | `tests/Feature/Security/PublicHashFoundationTest.php` |
| Tier 1 | `tests/Feature/Security/PublicHashTierOneTest.php` |
| Tier 2 | `tests/Feature/Security/PublicHashTierTwoTest.php` |
| Leak elimination | `tests/Feature/Security/PublicHashLeakEliminationTest.php` |
| External surface | `tests/Feature/Security/PublicHashExternalSurfaceTest.php` |
| Certification | `tests/Feature/Security/PublicHashCertificationTest.php` |

Tests that verify numeric fallback explicitly set `Config::set('public_hashes.numeric_fallback_enabled', true)`.

## Rollout phases (complete)

| Phase | Scope | Status |
|-------|-------|--------|
| PUBLIC-HASH-01 | Foundation | Certified |
| PUBLIC-HASH-02 | Tier 1 business models | Certified |
| PUBLIC-HASH-03 | Tier 2 operational models | Certified |
| PUBLIC-HASH-03A | Admin leak cleanup | Certified |
| PUBLIC-HASH-05 | Client portal, signed URLs | Certified |
| PUBLIC-HASH-06 | Fallback sunset, CI enforcement | **Certified** |
