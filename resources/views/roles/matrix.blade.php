@extends('layouts.app')

@section('title', 'Permissions Matrix')
@section('page_title', 'Access Control Matrix')

@section('content')
<div class="max-w-6xl mx-auto">

    <!-- Top Info -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-sm text-slate-400">Manage fine-grained capability checks by checking/unchecking permissions across system roles.</p>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <form action="/permissions/sync" method="POST">
        @csrf

        <!-- Matrix Card -->
        <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl overflow-hidden shadow-2xl mb-6">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/5 bg-slate-900/30 text-slate-400 text-xs font-bold uppercase tracking-wider">
                        <th class="px-6 py-4 w-1/3">Permission Name</th>
                        @foreach($roles as $role)
                            <th class="px-6 py-4 text-center">{{ $role->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-sm text-slate-200">
                    @foreach($permissions as $domain => $perms)
                        <!-- Domain Header Row -->
                        <tr class="bg-slate-950/40 font-bold text-xs uppercase tracking-wider text-indigo-400">
                            <td colspan="{{ count($roles) + 1 }}" class="px-6 py-3">
                                {{ $domain }}
                            </td>
                        </tr>
                        @foreach($perms as $perm)
                            <tr class="hover:bg-white/2 transition duration-150">
                                <td class="px-6 py-3.5 font-mono text-xs text-slate-300">
                                    {{ $perm->name }}
                                </td>
                                @foreach($roles as $role)
                                    <td class="px-6 py-3.5 text-center">
                                        @if($role->name === 'Admin')
                                            <!-- Admin always checked and disabled for safety -->
                                            <input type="checkbox" checked disabled
                                                class="rounded border-white/10 bg-slate-950 text-indigo-600 focus:ring-indigo-500 opacity-60 cursor-not-allowed">
                                            <!-- Hidden field to submit Admin permissions anyway -->
                                            <input type="hidden" name="matrix[{{ $role->id }}][]" value="{{ $perm->name }}">
                                        @else
                                            <input type="checkbox" name="matrix[{{ $role->id }}][]" value="{{ $perm->name }}"
                                                {{ $role->hasPermissionTo($perm->name) ? 'checked' : '' }}
                                                class="rounded border-white/10 bg-slate-950 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Submit Footer -->
        <div class="flex justify-end gap-3">
            <button type="submit" 
                class="px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-indigo-500/25 transition duration-200 transform hover:-translate-y-0.5 cursor-pointer">
                Save Matrix Changes
            </button>
        </div>

    </form>

</div>
@endsection
