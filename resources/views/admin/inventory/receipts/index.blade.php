<x-admin-layout :title="__('Receipts')">
    <x-admin.page-header :title="__('Stock receipts')">
        @can('create', App\Models\Inventory\StockReceipt::class)<a href="{{ route('admin.inventory.receipts.create') }}" class="erp-btn-primary">{{ __('New') }}</a>@endcan
    </x-admin.page-header>
    <x-admin.card><table class="erp-table w-full text-sm"><tbody>
        @foreach ($receipts as $r)<tr><td>{{ $r->receipt_number }}</td><td>{{ $r->status->value }}</td><td><a href="{{ route('admin.inventory.receipts.show', $r) }}">{{ __('View') }}</a></td></tr>@endforeach
    </tbody></table>{{ $receipts->links() }}</x-admin.card>
</x-admin-layout>
