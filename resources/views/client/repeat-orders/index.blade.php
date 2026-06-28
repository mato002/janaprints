<x-layouts.client :title="__('Repeat order')" :heading="__('Request repeat order')">
  @if (session('status'))
    <p class="client-flash mb-4">{{ session('status') }}</p>
  @endif

  <p class="client-lead mb-6">{{ __('Select a previous job and submit a repeat request. Our team will review and confirm before any new order is created.') }}</p>

  <div class="client-table-wrap">
    <table class="client-table">
      <thead>
        <tr>
          <th>{{ __('Order') }}</th>
          <th>{{ __('Date') }}</th>
          <th>{{ __('Total') }}</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($orders as $order)
          <tr>
            <td>{{ $order->order_number }}</td>
            <td>{{ $order->order_date?->format('M j, Y') }}</td>
            <td>KES {{ number_format((float) $order->total_amount, 0) }}</td>
            <td>
              <form method="post" action="{{ route('client.repeat-orders.store', $order) }}">
                @csrf
                <button type="submit" class="client-button client-button--small">{{ __('Request repeat') }}</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="client-empty">{{ __('No eligible orders yet.') }}</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $orders->links() }}
</x-layouts.client>
