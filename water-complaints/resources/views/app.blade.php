<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Water Complaints</title>

    <!-- AdminLTE and Bootstrap styles -->
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">

    <!-- AdminLTE FontAwesome (optional but useful) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        {{-- Navbar --}}
        @include('layouts.navbar')

        {{-- Sidebar --}}
        @include('layouts.sidebar')

        {{-- Content Wrapper --}}
        <div class="content-wrapper">
            {{-- Content Header --}}
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">@yield('page-title', 'Dashboard')</h1>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Content --}}
            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
        </div>

        {{-- Footer --}}
        @include('layouts.footer')
    </div>

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}"></script>
</body>
</html>
