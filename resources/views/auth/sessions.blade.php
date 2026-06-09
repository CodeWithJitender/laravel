@extends('layouts.app')

@section('title', 'Active Sessions')
@section('page_title', 'Active Devices & Sessions')

@section('content')
<div class="max-w-4xl mx-auto">

    <!-- Top Action -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-sm text-slate-400">View and manage all active browser sessions across your devices.</p>
        </div>
        
        <form action="/sessions/clear-all" method="POST" onsubmit="return confirm('Are you sure you want to log out of all other devices?')">
            @csrf
            <button type="submit" class="px-4 py-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 font-semibold rounded-xl border border-rose-500/20 text-xs transition duration-200 cursor-pointer">
                Terminate All Other Sessions
            </button>
        </form>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Table Card -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl overflow-hidden shadow-2xl">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-white/5 bg-slate-900/30 text-slate-400 text-xs font-bold uppercase tracking-wider">
                    <th class="px-6 py-4">Device & Browser</th>
                    <th class="px-6 py-4">IP Address</th>
                    <th class="px-6 py-4">Last Activity</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-sm text-slate-200">
                @foreach($sessions as $session)
                    <tr class="hover:bg-white/2 transition duration-150">
                        <td class="px-6 py-4 flex items-center gap-3">
                            <!-- Device Icon -->
                            <div class="w-9 h-9 rounded-lg bg-slate-800 flex items-center justify-center border border-white/5 text-slate-300">
                                @if($session['platform'] === 'iOS' || $session['platform'] === 'Android')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <span class="block font-semibold text-slate-200">{{ $session['platform'] }}</span>
                                <span class="block text-xs text-slate-400">{{ $session['browser'] }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-slate-300">
                            {{ $session['ip_address'] }}
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-400">
                            {{ $session['last_active'] }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($session['is_current'])
                                <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/10 text-xs font-semibold rounded-full select-none">
                                    Current Session
                                </span>
                            @else
                                <form action="/sessions/{{ $session['id'] }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to terminate this session?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 rounded-lg text-xs font-semibold border border-rose-500/10 transition duration-200 cursor-pointer">
                                        Log Out Device
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
