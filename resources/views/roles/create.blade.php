@extends('layouts.app')

@section('title', 'Create Role')
@section('page_title', 'Create Custom Role')

@section('content')
<div class="max-w-4xl mx-auto">
    
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-8 shadow-2xl relative overflow-hidden mb-6">
        <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] rounded-full bg-indigo-500/5 blur-[80px] pointer-events-none"></div>

        <h2 class="text-xl font-semibold mb-6 text-slate-200">Define Role Settings</h2>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('roles.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Role Name</label>
                    <input type="text" name="name" id="name" required value="{{ old('name') }}"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                        placeholder="e.g. Supervisor">
                </div>

                <div>
                    <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Role Description</label>
                    <input type="text" name="description" id="description" value="{{ old('description') }}"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                        placeholder="e.g. Can view direct reports but has no edit rights">
                </div>
            </div>

            <!-- Permissions Mapping Grid -->
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-300 mb-4 border-b border-white/5 pb-2">Assign Permissions</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($permissions as $domain => $perms)
                        <div class="p-5 bg-slate-900/40 rounded-2xl border border-white/5">
                            <span class="block text-xs font-bold uppercase tracking-wider text-indigo-400 mb-3">{{ ucfirst($domain) }}</span>
                            <div class="space-y-2">
                                @foreach($perms as $perm)
                                    <label class="flex items-center text-xs text-slate-300 cursor-pointer select-none">
                                        <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                                            class="mr-2.5 rounded border-white/10 bg-slate-950 text-indigo-600 focus:ring-indigo-500">
                                        <span>{{ $perm->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                <a href="{{ route('roles.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold border border-white/5 transition duration-200">
                    Cancel
                </a>
                <button type="submit" 
                    class="px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-indigo-500/25 transition duration-200 transform hover:-translate-y-0.5 cursor-pointer">
                    Save Role
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
