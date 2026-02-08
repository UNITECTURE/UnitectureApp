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

    {{-- Flash Messages --}}
    @if (session('success'))
    <div x-data="{ show: true }" 
            x-show="show" 
            x-init="setTimeout(() => show = false, 4000)"
            x-transition:enter="transition ease-out duration-500 cubic-bezier(0.175, 0.885, 0.32, 1.275)"
            x-transition:enter-start="opacity-0 translate-y-8 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-8"
            class="fixed inset-0 z-[150] flex items-center justify-center p-4">
        <div class="bg-green-50 text-green-900 px-6 py-4 rounded-xl border border-green-200 shadow-2xl flex items-center gap-4 border-l-4 border-l-green-500">
            <div class="bg-green-100 p-2 rounded-full ring-2 ring-green-100/50">
                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <span class="block text-sm font-bold text-green-900">Success!</span>
                <span class="text-sm font-medium text-green-800">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-green-600 hover:text-green-800 ml-2"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
    </div>
    @endif

    @if (session('error'))
    <div x-data="{ show: true }" 
            x-show="show" 
            x-init="setTimeout(() => show = false, 5000)"
            x-transition:enter="transition ease-out duration-500 cubic-bezier(0.175, 0.885, 0.32, 1.275)"
            x-transition:enter-start="opacity-0 translate-y-8 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-8"
            class="fixed inset-0 z-[150] flex items-center justify-center p-4">
        <div class="bg-red-50 text-red-900 px-6 py-4 rounded-xl border border-red-200 shadow-2xl flex items-center gap-4 border-l-4 border-l-red-500">
            <div class="bg-red-100 p-2 rounded-full ring-2 ring-red-100/50">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <span class="block text-sm font-bold text-red-900">Error!</span>
                <span class="text-sm font-medium text-red-800">{{ session('error') }}</span>
            </div>
            <button @click="show = false" class="text-red-600 hover:text-red-800 ml-2"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
    </div>
    @endif

    @if (session('info'))
    <div x-data="{ show: true }" 
            x-show="show" 
            style="position: fixed; bottom: 24px; right: 24px; z-index: 9999;"
            x-init="setTimeout(() => show = false, 4000)"
            x-transition:enter="transition ease-out duration-500 cubic-bezier(0.175, 0.885, 0.32, 1.275)"
            x-transition:enter-start="opacity-0 translate-y-8 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-8"
            class="fixed bottom-6 right-6 z-[150] bg-blue-50 text-blue-900 px-6 py-4 rounded-xl border border-blue-200 shadow-2xl flex items-center gap-4 border-l-4 border-l-blue-500">
        <div class="bg-blue-100 p-2 rounded-full ring-2 ring-blue-100/50">
            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <span class="block text-sm font-bold text-blue-900">Info</span>
            <span class="text-sm font-medium text-blue-800">{{ session('info') }}</span>
        </div>
        <button @click="show = false" class="text-blue-600 hover:text-blue-800 ml-2"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
    </div>
    @endif

    @if ($errors->any())
    <div x-data="{ show: true }" 
            x-show="show" 
            style="position: fixed; bottom: 24px; right: 24px; z-index: 9999;"
            x-init="setTimeout(() => show = false, 6000)"
            x-transition:enter="transition ease-out duration-500 cubic-bezier(0.175, 0.885, 0.32, 1.275)"
            x-transition:enter-start="opacity-0 translate-y-8 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-8"
            class="fixed bottom-6 right-6 z-[150] bg-red-50 text-red-900 px-6 py-4 rounded-xl border border-red-200 shadow-2xl gap-4 border-l-4 border-l-red-500">
            <div class="flex items-start gap-4">
            <div class="bg-red-100 p-2 rounded-full ring-2 ring-red-100/50 mt-0.5">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div class="flex-1">
                <span class="text-sm font-bold block mb-1 text-red-900">Check your input</span>
                <ul class="list-disc list-inside text-sm text-red-800">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button @click="show = false" class="text-red-600 hover:text-red-800 ml-2"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
    </div>
    @endif
</body>
</html>
