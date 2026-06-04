<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Enums\GlAccountStatus;
use App\Http\Controllers\Controller;
use App\Models\Accounting\GlAccount;
use App\Support\Accounting\ChartOfAccountsExplorerService;
use App\Support\Accounting\ChartOfAccountsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChartOfAccountsExplorerController extends Controller
{
    public function __construct(
        protected ChartOfAccountsExplorerService $explorer,
        protected ChartOfAccountsService $chartOfAccounts,
    ) {}

    public function groups(Request $request): JsonResponse
    {
        $this->authorize('viewAny', GlAccount::class);

        $typeId = $request->integer('type_id');

        if (! $typeId) {
            return response()->json(['groups' => []]);
        }

        return response()->json([
            'groups' => $this->explorer->groupsForType($typeId),
        ]);
    }

    public function accounts(Request $request): JsonResponse
    {
        $this->authorize('viewAny', GlAccount::class);

        $groupId = $request->integer('group_id');

        if (! $groupId) {
            return response()->json(['accounts' => []]);
        }

        return response()->json([
            'accounts' => $this->explorer->accountsForGroup($groupId),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $this->authorize('viewAny', GlAccount::class);

        return response()->json([
            'results' => $this->explorer->search($request->string('q')->toString()),
        ]);
    }

    public function panel(GlAccount $account): JsonResponse
    {
        $this->authorize('view', $account);

        return response()->json([
            'panel' => $this->explorer->accountPanel($account),
        ]);
    }

    public function deactivate(GlAccount $account): JsonResponse
    {
        $this->authorize('update', $account);

        $this->chartOfAccounts->updateAccount($account, [
            'status' => GlAccountStatus::Inactive,
        ]);

        return response()->json([
            'status' => GlAccountStatus::Inactive->value,
            'status_label' => GlAccountStatus::Inactive->label(),
        ]);
    }
}
