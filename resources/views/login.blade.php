<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Unitecture</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
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
                    placeholder="Enter your registered email" oninput="validateEmail(this)">
                <p id="email-error" class="text-xs text-red-500 mt-1 hidden">Email must contain '@' symbol.</p>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <div class="relative">
                    <input type="password" name="password" id="password" required 
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors outline-none pr-12"
                        placeholder="Enter your password">
                    <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-slate-500 hover:text-slate-700">
                        <span id="toggleText">show</span>
                    </button>
                </div>
            </div>

            <button type="submit" id="submit-btn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg transition-all shadow-sm hover:shadow-md active:scale-[0.98]">
                Sign In
            </button>

            <script>
                function togglePassword() {
                    const passwordInput = document.getElementById('password');
                    const toggleText = document.getElementById('toggleText');
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        toggleText.textContent = 'hide';
                    } else {
                        passwordInput.type = 'password';
                        toggleText.textContent = 'show';
                    }
                }

                function validateEmail(input) {
                    const errorMsg = document.getElementById('email-error');
                    if (!input.value.includes('@')) {
                        errorMsg.classList.remove('hidden');
                        input.classList.add('border-red-500');
                        input.classList.remove('focus:ring-blue-500', 'focus:border-blue-500');
                        input.classList.add('focus:ring-red-500', 'focus:border-red-500');
                    } else {
                        errorMsg.classList.add('hidden');
                        input.classList.remove('border-red-500');
                        input.classList.remove('focus:ring-red-500', 'focus:border-red-500');
                        input.classList.add('focus:ring-blue-500', 'focus:border-blue-500');
                    }
                }

                function checkPasswordStrength(password) {
                    const bars = [
                        document.getElementById('bar-1'),
                        document.getElementById('bar-2'),
                        document.getElementById('bar-3'),
                        document.getElementById('bar-4')
                    ];
                    const hint = document.getElementById('password-hint');
                    
                    let strength = 0;
                    
                    // Rules
                    const rules = [
                        password.length >= 8,
                        /[a-z]/.test(password),
                        /[A-Z]/.test(password),
                        /[0-9]/.test(password),
                        /^[a-zA-Z0-9!@#$%^]*$/.test(password) // Allowed chars check
                    ];

                    // Core requirements met?
                    const validChars = /^[a-zA-Z0-9!@#$%^]*$/.test(password);
                    const hasUpper = /[A-Z]/.test(password);
                    const hasLower = /[a-z]/.test(password);
                    const hasNumber = /[0-9]/.test(password);
                    const hasLength = password.length >= 8;

                    if (hasLength) strength++;
                    if (hasLower && hasUpper) strength++;
                    if (hasNumber) strength++;
                    if (password.length >= 12) strength++; // Bonus for length

                    // Reset bars
                    bars.forEach(bar => bar.className = 'h-full w-full rounded-full bg-slate-200 transition-colors duration-300');

                    // Validation Message Color
                    if (validChars && hasUpper && hasLower && hasNumber && hasLength) {
                        hint.classList.remove('text-red-500');
                        hint.classList.add('text-green-600');
                    } else {
                        hint.classList.add('text-red-500');
                        hint.classList.remove('text-green-600');
                    }

                    // Fill bars
                    const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
                    for (let i = 0; i < strength; i++) {
                        if (i < 4) {
                            bars[i].classList.remove('bg-slate-200');
                            bars[i].classList.add(colors[Math.min(strength - 1, 3)]);
                        }
                    }
                }
            </script>
        </form>
    </div>
</body>
</html>
