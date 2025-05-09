@extends('adminlte::page')

@section('title', 'All Users')


@section('content')
<div class="card">
    <div class="card-body">

    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary mb-3">← Back to Dashboard</a>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Registered At</th>
                    <th>Role</th>
                    <th>Department</th> {{-- New column --}}
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->created_at->format('Y-m-d') }}</td>
                    <td>{{ $user->role ?? 'N/A' }}</td>
                    <td>{{ $user->department->name ?? 'N/A' }}</td> {{-- Show department --}}
                    <td>
                        <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure to delete this user?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop