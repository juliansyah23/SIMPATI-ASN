<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIMPATI ASN') — Sistem Monitoring Psikososial ASN</title>

    {{-- Tailwind via CDN (swap for Vite + npm build in production) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#fef2f2',
                            100: '#fee2e2',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                            800: '#991b1b',
                        },
                    },
                },
            },
        };
    </script>

    {{-- Icons --}}
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    {{-- Charts --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>

    {{-- SweetAlert2 (popup notifikasi) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('styles')
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

    @include('partials.navbar')

    <main>
        @yield('content')
    </main>

    <script>
        // Render all lucide icons on initial load
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();
        });
    </script>

    @if (session('registered'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Registrasi Berhasil!',
                    text: @json(session('registered')),
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'Lanjutkan',
                });
            });
        </script>
    @endif

    @stack('scripts')
</body>
</html>
