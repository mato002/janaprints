@include('admin.employees.form', [
    'employee' => $employee,
    'action' => route('admin.employees.update', $employee),
    'method' => 'PUT',
    'communicationTimeline' => $communicationTimeline ?? collect(),
    'emailTimeline' => $emailTimeline ?? collect(),
])
