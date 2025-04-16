{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="min-h-screen bg-white dark:bg-gray-900 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-col items-center justify-content-center">
            <div class="max-w-7xl mx-auto text-center">
                <h1 class="text-4xl font-semibold text-gray-900 dark:text-white">
                    Welcome to the Dashboard
                </h1>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400 leading-relaxed">

                    This is your dashboard page where you can monitor and manage all water-related complaints in Nairobi.
                </p>
                <div class="mt-6 space-y-4">
                    <a href="{{ route('complaints.create') }}" class="inline-block px-6 py-3 text-sm font-semibold text-white bg-blue-600 rounded hover:bg-blue-700">
                        Submit a Complaint
                    </a>
                    <a href="{{ route('complaints.index') }}" class="inline-block px-6 py-3 ml-4 text-sm font-semibold text-white bg-blue-500 hover:bg-blue-600">
                        View Complaints
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    <footer class="main-footer text-center">
        <p class="text-muted">
            &copy; {{ date('Y') }} Nairobi Waters
        </p>
    </footer>
@endsection