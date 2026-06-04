<?php

namespace App\Http\Controllers\Admin\Accounting;

use App\Enums\JournalStatus;
use App\Http\Controllers\Admin\Accounting\Concerns\ResolvesAccountingTenant;
use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\GlAccount;
use App\Models\Accounting\Journal;
use App\Support\Accounting\JournalPostingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JournalController extends Controller
{
    use ResolvesAccountingTenant;

    public function __construct(
        protected JournalPostingService $journals,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Journal::class);

        $journals = Journal::query()
            ->forTenant()
            ->with(['accountingPeriod', 'creator'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest('journal_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.accounting.journals.index', compact('journals'));
    }

    public function create(): View
    {
        $this->authorize('create', Journal::class);

        return view('admin.accounting.journals.create', $this->formMeta());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Journal::class);

        $header = $this->validateHeader($request);
        $lines = $this->validateLines($request);

        $journal = $this->journals->createDraft($header, $lines, (int) auth()->id());

        return redirect()
            ->route('admin.accounting.journals.show', $journal)
            ->with('status', __('Journal draft created.'));
    }

    public function show(Journal $journal): View
    {
        $this->authorize('view', $journal);

        $journal->load([
            'lines.glAccount',
            'accountingPeriod',
            'fiscalYear',
            'creator',
            'poster',
            'reversalOf',
            'reversedBy.lines',
            'postingTemplate',
        ]);

        return view('admin.accounting.journals.show', compact('journal'));
    }

    public function edit(Journal $journal): View
    {
        $this->authorize('update', $journal);

        $journal->load('lines.glAccount');

        return view('admin.accounting.journals.edit', [
            'journal' => $journal,
            ...$this->formMeta($journal),
        ]);
    }

    public function update(Request $request, Journal $journal): RedirectResponse
    {
        $this->authorize('update', $journal);

        $header = $this->validateHeader($request);
        $lines = $this->validateLines($request);

        $this->journals->updateDraft($journal, $header, $lines);

        return redirect()
            ->route('admin.accounting.journals.show', $journal)
            ->with('status', __('Journal updated.'));
    }

    public function destroy(Journal $journal): RedirectResponse
    {
        $this->authorize('delete', $journal);

        $this->journals->deleteDraft($journal);

        return redirect()
            ->route('admin.accounting.journals.index')
            ->with('status', __('Journal deleted.'));
    }

    public function post(Journal $journal): RedirectResponse
    {
        $this->authorize('post', $journal);

        $this->journals->post($journal, (int) auth()->id());

        return back()->with('status', __('Journal posted to the general ledger.'));
    }

    public function reverse(Request $request, Journal $journal): RedirectResponse
    {
        $this->authorize('reverse', $journal);

        $validated = $request->validate([
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $reversal = $this->journals->reverse(
            $journal,
            (int) auth()->id(),
            $validated['description'] ?? null,
        );

        return redirect()
            ->route('admin.accounting.journals.show', $reversal)
            ->with('status', __('Reversal journal posted.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formMeta(?Journal $journal = null): array
    {
        ['companyId' => $companyId] = $this->tenantIds();

        $periods = AccountingPeriod::query()
            ->forTenant()
            ->with('fiscalYear')
            ->orderByDesc('start_date')
            ->get();

        $accounts = GlAccount::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->where('is_postable', true)
            ->where('status', 'active')
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        $currentPeriod = AccountingPeriod::query()
            ->forTenant()
            ->where('is_current', true)
            ->first();

        return [
            'periods' => $periods,
            'accounts' => $accounts,
            'currentPeriodId' => $journal?->accounting_period_id ?? $currentPeriod?->id,
            'defaultDate' => $journal?->journal_date?->format('Y-m-d') ?? now()->toDateString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateHeader(Request $request): array
    {
        return $request->validate([
            'accounting_period_id' => ['required', 'exists:accounting_periods,id'],
            'journal_date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function validateLines(Request $request): array
    {
        $request->validate([
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.gl_account_id' => ['required', 'exists:gl_accounts,id'],
            'lines.*.description' => ['nullable', 'string', 'max:255'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
        ]);

        return $request->input('lines', []);
    }
}
