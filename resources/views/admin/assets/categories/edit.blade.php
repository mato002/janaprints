@include('admin.assets.categories.partials.form', [
    'category' => $category,
    'action' => route('admin.assets.categories.update', $category),
    'method' => 'PUT',
    'title' => __('Edit asset category'),
])
