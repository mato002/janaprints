@php $versions = $tabData['versions'] ?? []; @endphp

<div class="overflow-x-auto">
    <table class="erp-table w-full text-sm">
        <thead>
            <tr>
                <th>{{ __('Version') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('User') }}</th>
                <th>{{ __('Reason') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($versions as $version)
                <tr>
                    <td>
                        <span class="font-medium">{{ $version['label'] }}</span>
                        @if ($version['is_current'])
                            <span class="erp-badge ml-1">{{ __('Current') }}</span>
                        @endif
                    </td>
                    <td>{{ $version['uploaded_at'] ? \Illuminate\Support\Carbon::parse($version['uploaded_at'])->format('Y-m-d H:i') : '—' }}</td>
                    <td>{{ $version['uploaded_by'] ?? '—' }}</td>
                    <td>{{ $version['change_notes'] }}</td>
                    <td>{{ $version['status'] }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-slate-500">{{ __('No artwork versions yet.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
