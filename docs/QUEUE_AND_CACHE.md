# Queue & Cache Architecture (Phase 9A)

## Queue names

Configured in `config/platform.php` under `queues`:

| Key | Default queue name | Typical jobs |
|-----|-------------------|--------------|
| `default` | `default` | General async work |
| `notifications` | `notifications` | In-app / database notifications |
| `emails` | `emails` | Mailables |
| `sms` | `sms` | SMS gateways |
| `documents` | `documents` | PDF generation, merges |
| `reports` | `reports` | Heavy report exports |
| `imports` | `imports` | Bulk imports |
| `exports` | `exports` | CSV/Excel exports |
| `integrations` | `integrations` | Webhooks, external APIs |

### Dispatching jobs

Extend `App\Jobs\PlatformJob` and select a queue in the constructor:

```php
class SendQuotationEmail extends PlatformJob
{
    public function __construct(public int $quotationId)
    {
        parent::__construct();
        $this->useQueue('emails');
    }

    public function handle(): void
    {
        // ...
    }
}
```

Or inline:

```php
SendQuotationEmail::dispatch($id)->onQueue(config('platform.queues.emails'));
```

### Worker examples (database driver)

```bash
php artisan queue:work database --queue=default
php artisan queue:work database --queue=emails,notifications,default
php artisan queue:work database --queue=reports --timeout=600
```

### Redis migration (later)

1. Set `QUEUE_CONNECTION=redis` in `.env`
2. Keep the same queue name strings — no job code changes required
3. Run separate workers per queue as above

## Cache categories

TTLs are defined in `config/platform.php` under `cache`. Use `App\Support\Platform\PlatformCacheService`:

```php
app(PlatformCacheService::class)->remember('branches', "{$companyId}", function () {
    return Branch::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
});
```

| Category | TTL (seconds) | Safe to cache |
|----------|---------------|---------------|
| `navigation` | 300 | Filtered menu per user/tenant/roles |
| `permissions` | 600 | Permission name lists (invalidate on role change) |
| `dashboard` | 60 | Aggregate KPI counts |
| `system_settings` | 900 | Setup key/value pairs |
| `branches` | 900 | Active branch lists |
| `departments` | 900 | Department lists |
| `lead_stages` | 900 | CRM stage definitions |
| `quotation_statuses` | 3600 | Enum labels / metadata |
| `inventory_categories` | 900 | Category picklists |

**Do not cache** live stock balances, GL balances, or open document totals without explicit invalidation on every posting event.

### Invalidation

Call `forget($category, $key)` when data changes, e.g. after updating roles:

```php
app(PlatformCacheService::class)->forget('navigation', "{$userId}:{$companyId}:{$branchId}:{$roleKey}");
```

Redis cache tags can be added when `CACHE_STORE=redis` is enabled in production.
