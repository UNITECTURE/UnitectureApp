<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Unitecture') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Custom Centered Loader -->
    <style>
        [x-cloak] { display: none !important; }
        .loader-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #2563EB;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const loader = document.createElement('div');
            loader.id = 'global-loader';
            loader.className = 'fixed inset-0 z-[200] bg-white/80 backdrop-blur-sm flex items-center justify-center hidden';
            loader.innerHTML = '<div class="loader-spinner"></div>';
            document.body.appendChild(loader);

            const showLoader = () => loader.classList.remove('hidden');

            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', () => showLoader());
            });

            document.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', (e) => {
                    const url = link.href; // Get absolute URL

                    // Ignore external links, new tabs, or hash links
                    if (!url || link.target === '_blank' || url.includes('#')) return;

                    // Ensure it's an internal link
                    if (!url.startsWith(window.location.origin)) return;

                    // Exclude download/export keywords
                    if (url.includes('export') || url.includes('download')) return;

                    showLoader();
                });
            });

            // Fix for Back Button (BFCache): Hide loader when page is shown again
            window.addEventListener('pageshow', (event) => {
                loader.classList.add('hidden');
            });
        });
    </script>

    <!-- Scripts -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 bg-[#F8F9FB]">
    <div id="app" class="relative">
        @yield('content')
    </div>

    {{-- Flash Messages - All popups removed, use inline messages at page top instead --}}

    {{-- Error popup removed - errors now shown inline at page top --}}
</body>
</html>
