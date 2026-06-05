@include('admin.assets.partials.form', [
    'asset' => $asset,
    'action' => route('admin.assets.update', $asset),
    'method' => 'PUT',
    'title' => __('Edit asset'),
])
