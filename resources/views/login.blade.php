<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Unitecture</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom Centered Loader -->
    <style>
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
                    const url = link.href;
                    if (!url || link.target === '_blank' || url.includes('#')) return;
                    if (!url.startsWith(window.location.origin)) return;
                    showLoader();
                });
            });

            // Fix for Back Button: Hide loader on page load/restore
            window.addEventListener('pageshow', () => {
                loader.classList.add('hidden');
            });
        });
    </script>
</head>
<body class="bg-[#F8F9FB] flex items-center justify-center h-screen font-sans">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-slate-100">
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo.png') }}" alt="Unitecture Logo" class="h-20 mx-auto mb-6">
            <h1 class="text-2xl font-bold text-slate-800">Welcome Back</h1>
            <p class="text-slate-500 mt-2 text-sm">Please sign in to your account</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 bg-red-50 text-red-600 px-4 py-3 rounded-lg text-sm border border-red-100">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-6">
            @csrf
            
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                <input type="email" name="email" id="email" required 
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors outline-none"
                    placeholder="name@company.com">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input type="password" name="password" id="password" required 
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors outline-none"
                    placeholder="••••••••">
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg transition-all shadow-sm hover:shadow-md active:scale-[0.98]">
                Sign In
            </button>
        </form>
    </div>
</body>
</html>
