@include('admin.users.form', ['user' => $user, 'action' => route('admin.users.update', $user), 'method' => 'PUT'])
