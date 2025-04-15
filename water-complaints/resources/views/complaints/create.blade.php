@extends('layouts.app')

@section('title', 'Create Complaint')

@section('content')
<div class="container mt-8">
    <div class="row justify-content-center align-items-center">
        <div class="col-lg-12 text-center">
            <h5 class="text-4xl font-semibold text-gray-800 dark:text-gray-200">Create Complaint</h5>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                Fill out the form below to create a new complaint.
            </p>
            <form method="POST" action="{{ route('complaints.store') }}">
                @csrf
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
@endsection