@extends('adminlte::page')

@section('content')
    <h3>Assign Department to User</h3>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary mb-3">← Back to User List</a>

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Name:</label>
            <input type="text" value="{{ $user->name }}" class="form-control" disabled>
        </div>

        <div class="form-group">
            <label for="department_id">Select Department</label>
            <select name="department_id" class="form-control">
                <option value="">-- None --</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" {{ $user->department_id == $department->id ? 'selected' : '' }}>
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="role">Assign Role</label>
            <select name="role" class="form-control">
                <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User (Customer)</option>
                <option value="officer" {{ $user->role === 'officer' ? 'selected' : '' }}>Officer</option>
                <option value="hod" {{ $user->role === 'hod' ? 'selected' : '' }}>Head of Department</option>
                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
        </div>

        <button class="btn btn-primary">Update</button>
    </form>
@endsection
