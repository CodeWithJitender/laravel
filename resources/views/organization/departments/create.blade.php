@extends('layouts.app')

@section('title', 'Create Department')
@section('page_title', 'Create Department')

@section('content')
<div class="max-w-3xl mx-auto">

    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-8 shadow-2xl relative overflow-hidden mb-6">
        <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] rounded-full bg-indigo-500/5 blur-[80px] pointer-events-none"></div>

        <h2 class="text-xl font-semibold mb-6 text-slate-200">Department Specifications</h2>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('departments.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="department_code" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Department Code</label>
                    <input type="text" name="department_code" id="department_code" required value="{{ old('department_code') }}"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                        placeholder="e.g. ENG-01">
                </div>

                <div>
                    <label for="department_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Department Name</label>
                    <input type="text" name="department_name" id="department_name" required value="{{ old('department_name') }}"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                        placeholder="e.g. Engineering">
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Description</label>
                <textarea name="description" id="description" rows="3"
                    class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                    placeholder="Provide a detailed description of the department functions..."></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="head_employee_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Department Head</label>
                    <select name="head_employee_id" id="head_employee_id"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm select-dark">
                        <option value="">Select Department Head (Optional)</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('head_employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }} ({{ $emp->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Status</label>
                    <select name="status" id="status" required
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm select-dark">
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                <a href="{{ route('departments.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold border border-white/5 transition duration-200">
                    Cancel
                </a>
                <button type="submit" 
                    class="px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-indigo-500/25 transition duration-200 transform hover:-translate-y-0.5 cursor-pointer">
                    Save Department
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
