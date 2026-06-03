<x-admin-layout :title="__('Adjustments')">
    <x-admin.page-header :title="__('Stock adjustments')">
        @can('create', App\Models\Inventory\StockAdjustment::class)<a href="{{ route('admin.inventory.adjustments.create') }}" class="erp-btn-primary">{{ __('New') }}</a>@endcan
    </x-admin.page-header>
    <x-admin.card><table class="erp-table w-full text-sm"><tbody>
        @foreach ($adjustments as $a)<tr><td>{{ $a->adjustment_number }}</td><td>{{ $a->status->value }}</td><td><a href="{{ route('admin.inventory.adjustments.show', $a) }}">{{ __('View') }}</a></td></tr>@endforeach
    </tbody></table>{{ $adjustments->links() }}</x-admin.card>
</x-admin-layout>
