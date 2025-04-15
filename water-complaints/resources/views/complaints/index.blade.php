@extends('layouts.app')

@section('title', 'Complaints')

@section('content')
<div class="container mt-8">
    <div class="row justify-content-center align-items-center">
        <div class="col-lg-12 text-center">
            <h5 class="text-4xl font-semibold text-gray-800 dark:text-gray-200">Complaints</h5>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                List of all water-related complaints.
            </p>
            <a href="{{ route('complaints.create') }}" class="btn btn-primary">
                Submit a Complaint
            </a>
        </div>
    </div>
@endsection