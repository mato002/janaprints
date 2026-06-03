<x-admin-layout :title="__('Issues')">
    <x-admin.page-header :title="__('Stock issues')">
        @can('create', App\Models\Inventory\StockIssue::class)<a href="{{ route('admin.inventory.issues.create') }}" class="erp-btn-primary">{{ __('New') }}</a>@endcan
    </x-admin.page-header>
    <x-admin.card><table class="erp-table w-full text-sm"><tbody>
        @foreach ($issues as $i)<tr><td>{{ $i->issue_number }}</td><td>{{ $i->status->value }}</td><td><a href="{{ route('admin.inventory.issues.show', $i) }}">{{ __('View') }}</a></td></tr>@endforeach
    </tbody></table>{{ $issues->links() }}</x-admin.card>
</x-admin-layout>
