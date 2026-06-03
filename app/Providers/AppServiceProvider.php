<?php

namespace App\Providers;

use App\Listeners\LogAuthenticationActivity;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Enums\LeadStatus;
use App\Models\Crm\Customer;
use App\Models\Crm\CustomerSegment;
use App\Models\Crm\CustomerActivity;
use App\Models\Crm\Lead;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Models\Sales\Quotation;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\InventoryReorderAlert;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\StockAdjustment;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\QualityCheck;
use App\Models\Platform\SettingsGovernance;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Enums\QuotationStatus;
use App\Policies\ArtworkRequestPolicy;
use App\Policies\ArtworkVersionPolicy;
use App\Policies\QuotationPolicy;
use App\Policies\InventoryItemPolicy;
use App\Policies\InventoryMovementPolicy;
use App\Policies\ProductionJobCardPolicy;
use App\Policies\StockAdjustmentPolicy;
use App\Policies\StockIssuePolicy;
use App\Policies\StockReceiptPolicy;
use App\Policies\ProductionQueuePolicy;
use App\Policies\QualityCheckPolicy;
use App\Policies\SettingsPolicy;
use App\Policies\SalesOrderPolicy;
use Illuminate\Support\Facades\Route;
use App\Policies\ActivityLogPolicy;
use App\Policies\CustomerActivityPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\CustomerSegmentPolicy;
use App\Policies\LeadPolicy;
use App\Policies\BranchPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use App\Support\AccessControl\PermissionCatalog;
use App\Support\AccessControl\RoleDeactivationRegistry;
use App\Support\AccessControl\RoleGovernancePresenter;
use App\Support\Platform\ApprovalRulesManager;
use App\Support\Platform\ApprovalRulesService;
use App\Support\Platform\FormSettingsService;
use App\Support\Platform\NumberGenerator;
use App\Support\Platform\NumberingSequenceManager;
use App\Support\Platform\NumberingService;
use App\Support\Platform\PlatformCacheService;
use App\Support\Platform\SettingsRegistry;
use App\Support\Platform\SystemSettingsManager;
use App\Support\Platform\SystemSettingsService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    protected array $policies = [
        User::class => UserPolicy::class,
        Company::class => CompanyPolicy::class,
        Branch::class => BranchPolicy::class,
        Department::class => DepartmentPolicy::class,
        Employee::class => EmployeePolicy::class,
        Role::class => RolePolicy::class,
        ActivityLog::class => ActivityLogPolicy::class,
        Customer::class => CustomerPolicy::class,
        CustomerSegment::class => CustomerSegmentPolicy::class,
        Lead::class => LeadPolicy::class,
        CustomerActivity::class => CustomerActivityPolicy::class,
        Quotation::class => QuotationPolicy::class,
        ArtworkRequest::class => ArtworkRequestPolicy::class,
        ArtworkVersion::class => ArtworkVersionPolicy::class,
        SalesOrder::class => SalesOrderPolicy::class,
        ProductionJobCard::class => ProductionJobCardPolicy::class,
        ProductionQueue::class => ProductionQueuePolicy::class,
        QualityCheck::class => QualityCheckPolicy::class,
        InventoryItem::class => InventoryItemPolicy::class,
        StockReceipt::class => StockReceiptPolicy::class,
        StockIssue::class => StockIssuePolicy::class,
        StockAdjustment::class => StockAdjustmentPolicy::class,
        InventoryMovement::class => InventoryMovementPolicy::class,
        SettingsGovernance::class => SettingsPolicy::class,
    ];

    public function register(): void
    {
        $this->app->singleton(NumberGenerator::class);
        $this->app->singleton(NumberingSequenceManager::class);
        $this->app->singleton(NumberingService::class);
        $this->app->singleton(SettingsRegistry::class);
        $this->app->singleton(SystemSettingsManager::class);
        $this->app->singleton(SystemSettingsService::class);
        $this->app->singleton(FormSettingsService::class);
        $this->app->singleton(ApprovalRulesManager::class);
        $this->app->singleton(ApprovalRulesService::class);
        $this->app->singleton(PermissionCatalog::class);
        $this->app->singleton(RoleDeactivationRegistry::class);
        $this->app->singleton(RoleGovernancePresenter::class);
        $this->app->singleton(PlatformCacheService::class);
    }

    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        Gate::before(function (User $user, string $ability) {
            if (! $user->is_active || $user->email_verified_at === null) {
                return false;
            }

            if ($user->hasRole('Super Admin')) {
                return true;
            }

            return null;
        });

        $this->assertProductionEnvironmentIsSafe();

        Event::listen(Login::class, [LogAuthenticationActivity::class, 'handleLogin']);
        Event::listen(Logout::class, [LogAuthenticationActivity::class, 'handleLogout']);

        View::composer('layouts.admin.partials.sidebar', function ($view) {
            $user = auth()->user();
            $companyId = tenant()->companyId() ?? $user?->company_id ?? 'none';
            $branchId = tenant()->branchId() ?? $user?->default_branch_id ?? 'none';
            $roleKey = $user?->roles->pluck('name')->sort()->implode('|') ?? 'guest';
            $cacheKey = "{$user?->id}:{$companyId}:{$branchId}:{$roleKey}";

            $view->with('navItems', app(PlatformCacheService::class)->remember(
                'navigation',
                $cacheKey,
                fn () => $this->filterNavigation(config('navigation')),
            ));
        });

        View::composer('admin.dashboard', function ($view) {
            $cache = app(PlatformCacheService::class);
            $companyId = tenant()->companyId() ?? auth()->user()?->company_id ?? 'all';
            $branchId = tenant()->branchId() ?? auth()->user()?->default_branch_id ?? 'all';

            $view->with('dashboard', $cache->remember(
                'dashboard',
                "{$companyId}:{$branchId}",
                fn () => $this->buildDashboardMetrics(),
            ));
        });
    }

    protected function buildDashboardMetrics(): array
    {
        $leadQuery = Lead::query()->forTenant();
        $customerQuery = Customer::query()->forTenant();

        $pipelineStages = [
            ['key' => 'quotation', 'label' => __('Quotation'), 'count' => 0],
            ['key' => 'approved', 'label' => __('Approved'), 'count' => 0],
            ['key' => 'artwork', 'label' => __('Artwork'), 'count' => 0],
            ['key' => 'printing', 'label' => __('Printing'), 'count' => 0],
            ['key' => 'finishing', 'label' => __('Finishing'), 'count' => 0],
            ['key' => 'dispatch', 'label' => __('Dispatch'), 'count' => 0],
        ];

        $pipelineTotal = max(1, array_sum(array_column($pipelineStages, 'count')));

        foreach ($pipelineStages as &$stage) {
            $stage['percent'] = (int) round(($stage['count'] / $pipelineTotal) * 100);
        }
        unset($stage);

        return [
            'kpis' => [
                'revenue_today' => ['label' => __('Revenue Today'), 'value' => '—', 'hint' => __('Finance module')],
                'open_quotes' => [
                    'label' => __('Open Quotes'),
                    'value' => (string) Quotation::query()->forTenant()->whereIn('status', [
                        QuotationStatus::Draft,
                        QuotationStatus::PendingApproval,
                        QuotationStatus::Sent,
                        QuotationStatus::Viewed,
                    ])->count(),
                    'hint' => __('Quotations'),
                ],
                'jobs_in_production' => [
                    'label' => __('Jobs In Production'),
                    'value' => (string) ProductionJobCard::query()->forTenant()
                        ->where('status', \App\Enums\ProductionJobCardStatus::InProduction)
                        ->count(),
                    'hint' => __('Production'),
                ],
                'receivables' => ['label' => __('Receivables'), 'value' => '—', 'hint' => __('Finance module')],
                'stock_alerts' => [
                    'label' => __('Stock Alerts'),
                    'value' => (string) InventoryReorderAlert::query()->forTenant()->where('is_resolved', false)->count(),
                    'hint' => __('Inventory'),
                ],
            ],
            'crm' => [
                'open_leads' => (clone $leadQuery)->where('status', LeadStatus::Open)->count(),
                'customers' => (clone $customerQuery)->count(),
            ],
            'pipeline' => $pipelineStages,
            'financial' => [
                'revenue_mtd' => '—',
                'expenses_mtd' => '—',
                'profit_mtd' => '—',
            ],
            'recent_activity' => ActivityLog::query()
                ->forTenant()
                ->with('user')
                ->latest('created_at')
                ->limit(8)
                ->get()
                ->map(fn (ActivityLog $log) => [
                    'user_name' => $log->user?->name,
                    'action' => $log->action,
                    'model_type' => $log->model_type,
                    'model_id' => $log->model_id,
                    'created_at' => $log->created_at,
                    'ip_address' => $log->ip_address,
                ])
                ->all(),
        ];
    }

    protected function filterNavigation(array $items): array
    {
        $filtered = [];

        foreach ($items as $item) {
            if (isset($item['children'])) {
                $children = array_values(array_filter($item['children'], function ($child) {
                    if (! empty($child['coming_soon'])) {
                        return true;
                    }

                    if (! empty($child['permission']) && ! $this->userCanNavPermission($child['permission'])) {
                        return false;
                    }

                    if (! empty($child['route']) && ! Route::has($child['route'])) {
                        return false;
                    }

                    return true;
                }));

                if ($children !== []) {
                    $item['children'] = $children;
                    $filtered[] = $item;
                }

                continue;
            }

            if (! empty($item['route']) && ! Route::has($item['route'])) {
                continue;
            }

            if (empty($item['permission']) || $this->userCanNavPermission($item['permission'])) {
                $filtered[] = $item;
            }
        }

        return $filtered;
    }

    protected function userCanNavPermission(string $permission): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if (str_contains($permission, '|')) {
            foreach (explode('|', $permission) as $segment) {
                if ($user->can(trim($segment))) {
                    return true;
                }
            }

            return false;
        }

        return $user->can($permission);
    }

    public static function navItemIsActive(array $child): bool
    {
        if (! empty($child['active_routes'])) {
            foreach ($child['active_routes'] as $pattern) {
                if (request()->routeIs($pattern)) {
                    return true;
                }
            }
        }

        if (empty($child['route'])) {
            return false;
        }

        $route = $child['route'];

        if (str_ends_with($route, '.index')) {
            return request()->routeIs(str_replace('.index', '.*', $route));
        }

        return request()->routeIs($route);
    }

    public static function navGroupIsOpen(array $item): bool
    {
        if (! isset($item['children'])) {
            return false;
        }

        foreach ($item['children'] as $child) {
            if (self::navItemIsActive($child)) {
                return true;
            }
        }

        return false;
    }

    protected function assertProductionEnvironmentIsSafe(): void
    {
        if (! $this->app->environment('production') || $this->app->runningUnitTests()) {
            return;
        }

        if (config('app.debug')) {
            throw new \RuntimeException('APP_DEBUG must be false in production.');
        }

        if (config('database.default') === 'mysql' && config('database.connections.mysql.password') === '') {
            throw new \RuntimeException('Database password must be configured in production.');
        }
    }
}
