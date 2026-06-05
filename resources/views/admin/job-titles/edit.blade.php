@include('admin.job-titles.form', [
    'action' => route('admin.job-titles.update', $jobTitle),
    'method' => 'PUT',
])
