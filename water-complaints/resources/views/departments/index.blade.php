@extends('adminlte::page')

@section('title', 'Departments')

@section('content_header')
    <h1>Departments</h1>
@endsection

@section('content')
    @include('flash::message')

    <div class="card">
        <div class="card-body">
            @include('departments.table')
        </div>
    </div>
@endsection
