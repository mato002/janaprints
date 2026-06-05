@include('admin.assets.categories.partials.form', [
    'category' => null,
    'action' => route('admin.assets.categories.store'),
    'method' => 'POST',
    'title' => __('New asset category'),
])
