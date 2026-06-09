@extends('layouts.app')

@section('title', 'Edit Designation')
@section('page_title', 'Edit Designation: ' . $designation->designation_name)

@section('content')
<div class="max-w-3xl mx-auto">

    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-8 shadow-2xl relative overflow-hidden mb-6">
        <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] rounded-full bg-indigo-500/5 blur-[80px] pointer-events-none"></div>

        <h2 class="text-xl font-semibold mb-6 text-slate-200">Designation Details</h2>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('designations.update', $designation->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="designation_code" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Designation Code</label>
                    <input type="text" name="designation_code" id="designation_code" required value="{{ old('designation_code', $designation->designation_code) }}"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                        placeholder="e.g. SWE-01">
                </div>

                <div>
                    <label for="designation_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Designation Name</label>
                    <input type="text" name="designation_name" id="designation_name" required value="{{ old('designation_name', $designation->designation_name) }}"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                        placeholder="e.g. Software Engineer">
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Description</label>
                <textarea name="description" id="description" rows="3"
                    class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                    placeholder="Provide job profile summary..."></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="level" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Hierarchy Level</label>
                    <select name="level" id="level" required
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm select-dark">
                        <option value="1" {{ old('level', $designation->level) == '1' ? 'selected' : '' }}>Level 1 (CEO / Top)</option>
                        <option value="2" {{ old('level', $designation->level) == '2' ? 'selected' : '' }}>Level 2 (Director)</option>
                        <option value="3" {{ old('level', $designation->level) == '3' ? 'selected' : '' }}>Level 3 (Manager)</option>
                        <option value="4" {{ old('level', $designation->level) == '4' ? 'selected' : '' }}>Level 4 (Team Lead)</option>
                        <option value="5" {{ old('level', $designation->level) == '5' ? 'selected' : '' }}>Level 5 (Employee)</option>
                    </select>
                </div>

                <div>
                    <label for="parent_designation_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Reports To (Parent)</label>
                    <select name="parent_designation_id" id="parent_designation_id"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm select-dark">
                        <option value="">Select Parent Designation (Optional)</option>
                        @foreach($parentDesignations as $pDesg)
                            <option value="{{ $pDesg->id }}" {{ old('parent_designation_id', $designation->hierarchy ? $designation->hierarchy->parent_designation_id : '') == $pDesg->id ? 'selected' : '' }}>{{ $pDesg->designation_name }} (Level {{ $pDesg->level }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Status</label>
                    <select name="status" id="status" required
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm select-dark">
                        <option value="active" {{ old('status', $designation->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $designation->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                <a href="{{ route('designations.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold border border-white/5 transition duration-200">
                    Cancel
                </a>
                <button type="submit" 
                    class="px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-indigo-500/25 transition duration-200 transform hover:-translate-y-0.5 cursor-pointer">
                    Update Designation
                </button>
            </div>

        </form>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const textarea = document.getElementById("description");
        textarea.value = `{!! addslashes($designation->description) !!}`;
    });
</script>
@endsection
