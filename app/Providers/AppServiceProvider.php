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
use App\Models\Pos\PosSale;
use App\Models\Crm\Lead;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Artwork\ArtworkVersion;
use App\Models\Sales\Quotation;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\InventoryReorderAlert;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\StockReceipt;
use App\Models\Inventory\StockAdjustment;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Models\Production\QualityCheck;
use App\Models\Production\WorkCenter;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\Rfq;
use App\Models\Procurement\SupplierQuotation;
use App\Models\Platform\SettingsGovernance;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\Journal;
use App\Models\Accounting\PostingRule;
use App\Models\Accounting\PostingTemplate;
use App\Models\Tax\TaxCode;
use App\Models\Sales\CustomerInvoice;
use App\Models\Sales\CustomerPayment;
use App\Models\Procurement\Vendor;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Enums\QuotationStatus;
use App\Policies\ArtworkRequestPolicy;
use App\Policies\ArtworkVersionPolicy;
use App\Policies\QuotationPolicy;
use App\Policies\InventoryItemPolicy;
use App\Policies\InventoryMovementPolicy;
use App\Policies\WarehousePolicy;
use App\Policies\ProductionJobCardPolicy;
use App\Policies\StockAdjustmentPolicy;
use App\Policies\StockIssuePolicy;
use App\Policies\StockReceiptPolicy;
use App\Policies\ProductionQueuePolicy;
use App\Policies\QualityCheckPolicy;
use App\Policies\WorkCenterPolicy;
use App\Policies\GoodsReceiptPolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\PurchaseRequestPolicy;
use App\Policies\RfqPolicy;
use App\Policies\SupplierQuotationPolicy;
use App\Policies\AccountingPeriodPolicy;
use App\Policies\FiscalYearPolicy;
use App\Policies\GlAccountPolicy;
use App\Policies\JournalPolicy;
use App\Policies\CustomerInvoicePolicy;
use App\Policies\CustomerPaymentPolicy;
use App\Policies\PostingRulePolicy;
use App\Policies\PostingTemplatePolicy;
use App\Policies\TaxCodePolicy;
use App\Policies\VendorPolicy;
use App\Policies\SalesOrderPolicy;
use Illuminate\Support\Facades\Route;
use App\Policies\DeliveryNotePolicy;
use App\Policies\ActivityLogPolicy;
use App\Policies\CustomerActivityPolicy;
use App\Policies\PosSalePolicy;
use App\Policies\CustomerPolicy;
use App\Policies\CustomerSegmentPolicy;
use App\Policies\LeadPolicy;
use App\Policies\BranchPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\RolePolicy;
use App\Policies\SettingsPolicy;
use App\Policies\UserPolicy;
use App\Support\Navigation\WorkspacePresenter;
use App\View\Composers\WorkspaceNavigationComposer;
use App\Support\AccessControl\PermissionCatalog;
use App\Support\AccessControl\RoleDeactivationRegistry;
use App\Support\AccessControl\RoleGovernancePresenter;
use App\Support\Platform\ApprovalRulesManager;
use App\Support\Platform\ApprovalRulesService;
use App\Support\Platform\FormSettingsService;
use App\Support\Platform\NumberGenerator;
use App\Support\Platform\NumberingSequenceManager;
use App\Support\Platform\NumberingService;
use App\Support\Branding\BrandingAssets;
use App\Support\Dashboard\ExecutiveDashboardPresenter;
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
        PosSale::class => PosSalePolicy::class,
        \App\Models\Pos\PosReturn::class => \App\Policies\PosReturnPolicy::class,
        \App\Models\Pos\PosSession::class => \App\Policies\PosSessionPolicy::class,
        \App\Models\Pos\PosCashReconciliation::class => \App\Policies\PosCashReconciliationPolicy::class,
        \App\Models\Commercial\CommercialPriceBook::class => \App\Policies\CommercialPriceBookPolicy::class,
        \App\Models\Commercial\CommercialComplaint::class => \App\Policies\CommercialComplaintPolicy::class,
        \App\Models\Commercial\CommercialSupportTicket::class => \App\Policies\CommercialSupportTicketPolicy::class,
        Quotation::class => QuotationPolicy::class,
        ArtworkRequest::class => ArtworkRequestPolicy::class,
        ArtworkVersion::class => ArtworkVersionPolicy::class,
        SalesOrder::class => SalesOrderPolicy::class,
        ProductionJobCard::class => ProductionJobCardPolicy::class,
        ProductionQueue::class => ProductionQueuePolicy::class,
        QualityCheck::class => QualityCheckPolicy::class,
        WorkCenter::class => WorkCenterPolicy::class,
        InventoryItem::class => InventoryItemPolicy::class,
        Warehouse::class => WarehousePolicy::class,
        StockReceipt::class => StockReceiptPolicy::class,
        StockIssue::class => StockIssuePolicy::class,
        StockAdjustment::class => StockAdjustmentPolicy::class,
        InventoryMovement::class => InventoryMovementPolicy::class,
        Vendor::class => VendorPolicy::class,
        GlAccount::class => GlAccountPolicy::class,
        FiscalYear::class => FiscalYearPolicy::class,
        AccountingPeriod::class => AccountingPeriodPolicy::class,
        Journal::class => JournalPolicy::class,
        PostingRule::class => PostingRulePolicy::class,
        PostingTemplate::class => PostingTemplatePolicy::class,
        CustomerInvoice::class => CustomerInvoicePolicy::class,
        CustomerPayment::class => CustomerPaymentPolicy::class,
        \App\Models\Procurement\SupplierBill::class => \App\Policies\SupplierBillPolicy::class,
        \App\Models\Procurement\SupplierPayment::class => \App\Policies\SupplierPaymentPolicy::class,
        TaxCode::class => TaxCodePolicy::class,
        PurchaseRequest::class => PurchaseRequestPolicy::class,
        PurchaseOrder::class => PurchaseOrderPolicy::class,
        GoodsReceipt::class => GoodsReceiptPolicy::class,
        SupplierQuotation::class => SupplierQuotationPolicy::class,
        Rfq::class => RfqPolicy::class,
        DeliveryNote::class => DeliveryNotePolicy::class,
        SettingsGovernance::class => SettingsPolicy::class,
        \App\Models\Communications\CommunicationTemplate::class => \App\Policies\CommunicationTemplatePolicy::class,
        \App\Models\Communications\ErpNotification::class => \App\Policies\ErpNotificationPolicy::class,
        \App\Models\Communications\SmsCampaign::class => \App\Policies\SmsCampaignPolicy::class,
        \App\Models\Communications\CommunicationLog::class => \App\Policies\CommunicationLogPolicy::class,
        \App\Models\Communications\WhatsappConversation::class => \App\Policies\WhatsappConversationPolicy::class,
        \App\Models\Communications\EmailCampaign::class => \App\Policies\EmailCampaignPolicy::class,
        \App\Models\Communications\Inbox\CommunicationConversation::class => \App\Policies\CommunicationConversationPolicy::class,
    ];

    public function register(): void
    {
        $this->app->singleton(NumberGenerator::class);
        $this->app->singleton(NumberingSequenceManager::class);
        $this->app->singleton(NumberingService::class);
        $this->app->singleton(SettingsRegistry::class);
        $this->app->singleton(SystemSettingsManager::class);
        $this->app->singleton(SystemSettingsService::class);
        $this->app->singleton(ExecutiveDashboardPresenter::class);
        $this->app->singleton(\App\Support\Accounting\Dashboard\AccountingDashboardPresenter::class);
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
        \Illuminate\Support\Facades\Route::bind('notification', function (string $value) {
            return \App\Models\Communications\ErpNotification::query()->findOrFail($value);
        });

        \Illuminate\Support\Facades\Route::bind('campaign', function (string $value) {
            return \App\Models\Communications\SmsCampaign::query()->findOrFail($value);
        });

        \Illuminate\Support\Facades\Route::bind('communicationLog', function (string $value) {
            return \App\Models\Communications\CommunicationLog::query()->findOrFail($value);
        });

        \Illuminate\Support\Facades\Route::bind('conversation', function (string $value) {
            return \App\Models\Communications\WhatsappConversation::query()->findOrFail($value);
        });

        \Illuminate\Support\Facades\Route::bind('message', function (string $value) {
            return \App\Models\Communications\WhatsappMessage::query()->findOrFail($value);
        });

        \Illuminate\Support\Facades\Route::bind('emailCampaign', function (string $value) {
            return \App\Models\Communications\EmailCampaign::query()->findOrFail($value);
        });

        \Illuminate\Support\Facades\Route::bind('emailMessage', function (string $value) {
            return \App\Models\Communications\EmailMessage::query()->findOrFail($value);
        });

        \Illuminate\Support\Facades\Route::bind('inboxConversation', function (string $value) {
            return \App\Models\Communications\Inbox\CommunicationConversation::query()->findOrFail($value);
        });

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

        View::composer('layouts.admin', WorkspaceNavigationComposer::class);

        View::composer(['layouts.admin', 'layouts.admin.partials.sidebar', 'layouts.admin.partials.topbar'], function ($view) {
            $assets = app(BrandingAssets::class);
            $user = auth()->user();
            $company = tenant()->company ?? $user?->company;

            $view->with([
                'brandingLogoUrl' => $assets->url($company?->logo),
                'brandingFaviconUrl' => $assets->url($company?->favicon_path),
                'userAvatarUrl' => $assets->url($user?->avatar_path),
            ]);
        });

        View::composer(['layouts.admin', 'layouts.admin.partials.sidebar'], function ($view) {
            $user = auth()->user();
            $companyId = tenant()->companyId() ?? $user?->company_id ?? 'none';
            $branchId = tenant()->branchId() ?? $user?->default_branch_id ?? 'none';
            $roleKey = $user?->roles->pluck('name')->sort()->implode('|') ?? 'guest';
            $cacheKey = "{$user?->id}:{$companyId}:{$branchId}:{$roleKey}";

            $navItems = app(PlatformCacheService::class)->remember(
                'navigation',
                $cacheKey,
                fn () => $this->filterNavigation(config('navigation')),
            );

            $presenter = app(WorkspacePresenter::class);

            $view->with([
                'navItems' => $navItems,
                'navSearchIndex' => $presenter->flattenForSearch(),
                'navRouteUrls' => static::buildNavRouteUrls($navItems, $presenter),
            ]);
        });

        View::composer('layouts.admin.partials.topbar', function ($view) {
            $currentRoute = request()->route()?->getName();
            $quickCreate = app(WorkspacePresenter::class)->quickCreateForRoute($currentRoute);
            $user = auth()->user();
            $canNotifications = $user?->can('communications.notifications.view') ?? false;
            $unreadCount = 0;

            if ($canNotifications && $user) {
                $unreadCount = app(\App\Support\Communications\NotificationService::class)
                    ->unreadCount($user, tenant()->companyId() ?? $user->company_id);
            }

            $view->with([
                'quickCreate' => $quickCreate,
                'canNotifications' => $canNotifications,
                'notificationBellBootstrap' => [
                    'enabled' => $canNotifications,
                    'unreadCount' => $unreadCount,
                    'routes' => [
                        'panel' => route('admin.communications.notifications.bell.panel'),
                        'unread' => route('admin.communications.notifications.bell.unread'),
                        'markRead' => route('admin.communications.notifications.bell.mark-read', ['notification' => '__ID__']),
                        'markAllRead' => route('admin.communications.notifications.mark-all-read'),
                        'open' => route('admin.communications.notifications.open', ['notification' => '__ID__']),
                        'center' => route('admin.communications.notifications.index'),
                    ],
                ],
            ]);
        });

        View::composer('admin.dashboard', function ($view) {
            $cache = app(PlatformCacheService::class);
            $companyId = tenant()->companyId() ?? auth()->user()?->company_id ?? 'all';
            $branchId = tenant()->branchId() ?? auth()->user()?->default_branch_id ?? 'all';

            $view->with('dashboard', $cache->remember(
                'dashboard',
                "{$companyId}:{$branchId}",
                fn () => app(ExecutiveDashboardPresenter::class)->build(),
            ));
        });
    }

    protected function filterNavigation(array $items): array
    {
        $presenter = app(WorkspacePresenter::class);
        $filtered = [];

        foreach ($items as $item) {
            if (! empty($item['workspace'])) {
                if (! $presenter->isVisible($item['workspace'])) {
                    continue;
                }

                $item['active_routes'] = $presenter->collectActiveRoutes($item['workspace']);
                $filtered[] = $item;

                continue;
            }

            if (isset($item['children'])) {
                $children = $this->filterNavigation($item['children']);

                if ($children === []) {
                    continue;
                }

                $item['children'] = $children;
                $filtered[] = $item;

                continue;
            }

            if (! empty($item['coming_soon'])) {
                $filtered[] = $item;

                continue;
            }

            if (! empty($item['route']) && ! Route::has($item['route'])) {
                continue;
            }

            if (! empty($item['permission']) && ! $this->userCanNavPermission($item['permission'])) {
                continue;
            }

            $filtered[] = $item;
        }

        return $filtered;
    }

    /**
     * @return array<string, string>
     */
    public static function buildNavRouteUrls(array $items, ?WorkspacePresenter $presenter = null): array
    {
        $presenter ??= app(WorkspacePresenter::class);
        $map = [];

        foreach ($presenter->flattenForSearch() as $entry) {
            $route = $entry['route'] ?? null;

            if ($entry['coming_soon'] || ! $route || isset($map[$route]) || ! Route::has($route)) {
                continue;
            }

            $params = $entry['route_params'] ?? [];
            $map[$route] = route($route, $params);
        }

        foreach ($items as $item) {
            $route = $item['route'] ?? null;

            if (! $route || isset($map[$route]) || ! Route::has($route)) {
                continue;
            }

            $map[$route] = route($route);
        }

        return $map;
    }

    /**
     * @return list<array{label: string, path: string, route: ?string, coming_soon: bool}>
     */
    public static function flattenNavForSearch(array $items, string $path = ''): array
    {
        $flat = [];

        foreach ($items as $item) {
            $label = $item['label'] ?? '';
            $currentPath = $path === '' ? $label : "{$path} › {$label}";

            if (isset($item['children'])) {
                $flat = array_merge($flat, static::flattenNavForSearch($item['children'], $currentPath));

                continue;
            }

            $flat[] = [
                'label' => $label,
                'path' => $currentPath,
                'route' => $item['route'] ?? null,
                'coming_soon' => (bool) ($item['coming_soon'] ?? false),
            ];
        }

        return $flat;
    }

    /**
     * @return list<string>
     */
    public static function collectNavRoutes(array $item): array
    {
        $routes = [];

        foreach ($item['children'] ?? [] as $child) {
            if (isset($child['children'])) {
                $routes = array_merge($routes, static::collectNavRoutes($child));
            } elseif (! empty($child['route'])) {
                $routes[] = $child['route'];
            }

            foreach ($child['active_routes'] ?? [] as $pattern) {
                $routes[] = $pattern;
            }
        }

        return array_values(array_unique($routes));
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
            if (isset($child['children']) && self::navGroupIsOpen($child)) {
                return true;
            }

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
