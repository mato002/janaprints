# Jana Prints ERP — Query Performance Standards

Phase 9A project-wide rules for controllers, views, and services.

## Controllers

1. **Paginate all list/index pages** — use `paginate()` with sizes from `config/platform.php`
2. **Dashboards use summarized queries** — `count()`, `sum()`, grouped aggregates; never load full collections for KPIs
3. **Eager load only what the view needs** — `with([...])` on index/show; avoid `with()` on everything
4. **Use `select()`** on large tables when only a few columns are displayed
5. **No N+1** — audit with Laravel Debugbar or `Model::preventLazyLoading()` in local env
6. **Tenant scope every query** — `forTenant()` or `scopeToTenant()` on tenant-owned models

## Blade views

1. **No heavy calculations in Blade** — compute in controller or service
2. **No global wildcard view composers** — scope composers to specific views (see `AppServiceProvider`)
3. **Use partials** for repeated widgets
4. **Paginate** — always render `{{ $collection->links() }}` on index pages

## Caching

1. Use `PlatformCacheService` with categories from `config/platform.php`
2. Cache: navigation, permissions, dashboard widgets, settings, reference data (branches, departments, lead stages, categories)
3. **Do not cache** transactional stock balances unless invalidation is explicit and tested

## Queues

1. Extend `App\Jobs\PlatformJob` for async work
2. Assign queue via `$this->useQueue('reports')` etc.
3. Default connection: `database`; prepare for `redis` via env only

## Forms

1. Read field rules from `FormSettingsService`
2. Read defaults/validity/tax from `SystemSettingsService`
3. Generate document numbers only via `NumberingService`

## Approvals

1. Check `ApprovalRulesService::requiresApproval()` before auto-approving
2. Do not hard-code thresholds in controllers

## Index pages checklist

- [ ] Paginated
- [ ] Tenant scoped
- [ ] Minimal columns selected
- [ ] Relationships eager loaded
- [ ] Filters use indexed columns (status, dates, company_id, branch_id)
