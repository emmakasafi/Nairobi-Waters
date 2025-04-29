<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Corrected Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Corrected Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- AdminLTE CSS -->
    <link href="{{ asset('public/vendor/adminlte/dist/css/adminlte.min.css') }}" rel="stylesheet">

    <!-- Additional Styles (if any) -->
    @livewireStyles
</head>

<body class="bg-gray-100">
    <div class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900">
        @include('layouts.partials.header')

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            @yield('content')
        </div>
        <!-- /.Content Wrapper -->

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar dark">
            <!-- Control Sidebar Content Goes Here -->
        </aside>
        <!-- /.control-sidebar -->

        <!-- Main Footer -->
        <footer class="main-footer text-center">
            @include('layouts.partials.footer')
        </footer>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('public/vendor/adminlte/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('public/vendor/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('public/vendor/adminlte/dist/js/adminlte.min.js') }}"></script>

    @livewireScripts
</body>

</html>