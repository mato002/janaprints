@include('admin.assets.partials.form', [
    'asset' => null,
    'action' => route('admin.assets.store'),
    'method' => 'POST',
    'title' => __('Register asset'),
])
