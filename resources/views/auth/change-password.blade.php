@extends('layouts.app')

@section('title', 'Change Password')
@section('page_title', 'Change Password')

@section('content')
<div class="max-w-md mx-auto mt-10">
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
        <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] rounded-full bg-indigo-500/5 blur-[80px] pointer-events-none"></div>

        <h2 class="text-xl font-semibold mb-6 text-slate-200">Update Your Password</h2>

        <!-- Errors / Success -->
        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <form action="/change-password" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="current_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Current Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </span>
                    <input type="password" name="current_password" id="current_password" required 
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 pl-10 pr-10 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                        placeholder="••••••••">
                    <button type="button" onclick="togglePasswordVisibility('current_password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div>
                <label for="new_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">New Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </span>
                    <input type="password" name="new_password" id="new_password" required 
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 pl-10 pr-10 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                        placeholder="••••••••">
                    <button type="button" onclick="togglePasswordVisibility('new_password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div>
                <label for="new_password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Confirm New Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </span>
                    <input type="password" name="new_password_confirmation" id="new_password_confirmation" required 
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 pl-10 pr-10 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                        placeholder="••••••••">
                    <button type="button" onclick="togglePasswordVisibility('new_password_confirmation', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" 
                class="w-full py-3 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-medium rounded-2xl shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 transition duration-300 transform hover:-translate-y-0.5 text-sm cursor-pointer">
                Change Password
            </button>
        </form>
    </div>
</div>
<script>
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
@endsection
