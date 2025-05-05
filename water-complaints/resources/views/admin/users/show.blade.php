@extends('adminlte::page')

@section('title', 'User Details')

@section('content_header')
    <h1>User Details</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <p><strong>ID:</strong> {{ $user->id }}</p>
        <p><strong>Name:</strong> {{ $user->name }}</p>
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>Role:</strong> {{ $user->role ?? 'N/A' }}</p>
        <p><strong>Registered At:</strong> {{ $user->created_at->format('Y-m-d H:i') }}</p>
        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning">Edit</a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>
@stop
