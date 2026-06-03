@include('admin.departments.form', ['department' => $department, 'action' => route('admin.departments.update', $department), 'method' => 'PUT'])
