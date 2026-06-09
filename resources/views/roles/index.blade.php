@extends('layouts.app')

@section('title', 'Role Management')
@section('page_title', 'Role Management')

@section('content')
<div class="max-w-6xl mx-auto">

    <!-- Top Action -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-sm text-slate-400">Manage your system roles, descriptions, and their granular capability mappings.</p>
        </div>
        
        <a href="/roles/create" class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-indigo-500/25 transition duration-200 transform hover:-translate-y-0.5 cursor-pointer">
            + Create Role
        </a>
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
                    <th class="px-6 py-4">Role Name</th>
                    <th class="px-6 py-4">Description</th>
                    <th class="px-6 py-4 text-center">Active Users</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-sm text-slate-200">
                @foreach($roles as $role)
                    <tr class="hover:bg-white/2 transition duration-150">
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 bg-indigo-500/10 text-indigo-400 border border-indigo-500/10 text-xs font-bold rounded-lg uppercase tracking-wide">
                                {{ $role->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-300">
                            {{ $role->description ?? 'No description provided.' }}
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-slate-300">
                            {{ $role->users_count }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="/roles/{{ $role->id }}/edit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-xs font-semibold border border-white/5 transition duration-200 inline-block mr-2 cursor-pointer">
                                Edit
                            </a>
                            @if(!in_array($role->name, ['Admin', 'Manager', 'Employee']))
                                <form action="/roles/{{ $role->id }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this custom role?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 rounded-lg text-xs font-semibold border border-rose-500/10 transition duration-200 cursor-pointer">
                                        Delete
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
