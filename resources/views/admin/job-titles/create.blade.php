@include('admin.job-titles.form', [
    'jobTitle' => null,
    'action' => route('admin.job-titles.store'),
    'method' => 'POST',
])
