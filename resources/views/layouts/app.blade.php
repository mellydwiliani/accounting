
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Accounting')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- CSS Utama -->
    <link rel="stylesheet" href="{{ asset('css/accounting.css') }}">
    <!-- CSS tambahan khusus halaman -->
    @stack('styles')
</head>

<body>

    <div class="layout">

        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Konten Utama -->
        <main class="main">

            @yield('content')

        </main>

    </div>

    <!-- JavaScript tambahan -->
    @stack('scripts')

</body>
</html>
