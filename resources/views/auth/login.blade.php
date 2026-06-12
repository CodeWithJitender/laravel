<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | HRMS Enterprise</title>
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center relative overflow-hidden">

    <!-- Decorative background glowing gradients -->
    <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] rounded-full bg-indigo-500/10 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-20%] right-[-10%] w-[50%] h-[50%] rounded-full bg-fuchsia-500/10 blur-[120px] pointer-events-none"></div>

    <div class="w-full max-w-md p-6 z-10">
        <!-- Logo / Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-600 shadow-lg shadow-indigo-500/30 mb-4 animate-pulse">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold tracking-tight bg-gradient-to-r from-white via-slate-200 to-slate-400 bg-clip-text text-transparent">HRMS Enterprise</h1>
            <p class="text-slate-400 mt-2 text-sm">Human Resource Management System</p>
        </div>

        <!-- Glassmorphism Login Card -->
        <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
            
            <h2 class="text-xl font-semibold mb-6 text-center text-slate-200">Sign in to your account</h2>

            <!-- Session Status / Errors -->
            @if($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="/login" method="POST" class="space-y-5" id="loginForm">
                @csrf
                
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Email Address</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </span>
                        <input type="email" name="email" id="email" required 
                            class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 pl-10 pr-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                            placeholder="username@company.com">
                    </div>
                </div>

                <div>
                    <div class="flex justify-between mb-2">
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Password</label>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </span>
                        <input type="password" name="password" id="password" required 
                            class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 pl-10 pr-10 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                            placeholder="••••••••">
                        <button type="button" onclick="togglePasswordVisibility('password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center text-xs text-slate-400 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="mr-2 rounded border-white/10 bg-slate-900 focus:ring-indigo-500">
                        Remember me
                    </label>
                </div>

                <button type="submit" 
                    class="w-full py-3 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-medium rounded-2xl shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 transition duration-300 transform hover:-translate-y-0.5 text-sm cursor-pointer">
                    Sign In
                </button>
            </form>

            <div class="relative flex py-4 items-center">
                <div class="flex-grow border-t border-white/5"></div>
                <span class="flex-shrink mx-4 text-slate-500 text-xs font-medium uppercase tracking-wider">Demo Access</span>
                <div class="flex-grow border-t border-white/5"></div>
            </div>

            <!-- Demo Login Shortcuts -->
            <div class="grid grid-cols-3 gap-2">
                <button type="button" onclick="fillDemo('admin@company.com')" 
                    class="py-2.5 px-2 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 rounded-xl text-xs font-semibold text-indigo-300 hover:text-indigo-200 transition duration-200 cursor-pointer">
                    Admin
                </button>
                <button type="button" onclick="fillDemo('manager@company.com')" 
                    class="py-2.5 px-2 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 rounded-xl text-xs font-semibold text-purple-300 hover:text-purple-200 transition duration-200 cursor-pointer">
                    Manager
                </button>
                <button type="button" onclick="fillDemo('employee@company.com')" 
                    class="py-2.5 px-2 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 rounded-xl text-xs font-semibold text-fuchsia-300 hover:text-fuchsia-200 transition duration-200 cursor-pointer">
                    Employee
                </button>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-xs text-slate-500 mt-8">
            &copy; 2026 HRMS Enterprise. All rights reserved.
        </p>
    </div>

    <script>
        function fillDemo(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password';
        }

        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const svg = button.querySelector('svg');
            if (input.type === 'password') {
                input.type = 'text';
                svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>';
            } else {
                input.type = 'password';
                svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
            }
        }
    </script>
</body>
</html>
