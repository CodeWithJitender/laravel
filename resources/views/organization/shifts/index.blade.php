@extends('layouts.app')

@section('title', 'Shifts')
@section('page_title', 'Shift Management')

@section('content')
<div class="max-w-6xl mx-auto">

    <!-- Top Actions -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-sm text-slate-400">Configure standard working shifts, timing slots, grace period tolerances, and break allocations.</p>
        </div>
        
        @can('shift.create')
        <a href="{{ route('shifts.create') }}" class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-indigo-500/25 transition duration-200 transform hover:-translate-y-0.5 cursor-pointer">
            + Add Shift
        </a>
        @endcan
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Search & Filters -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-6 shadow-2xl mb-6">
        <form action="{{ route('shifts.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-grow">
                <label for="search" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Search Shifts</label>
                <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Search by name or code..."
                    class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2.5 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm">
            </div>

            <div class="w-full md:w-48 shrink-0">
                <label for="status" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Filter by Status</label>
                <select name="status" id="status" class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-2.5 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm">
                    <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="flex gap-2 w-full md:w-auto">
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-xs transition duration-200 cursor-pointer w-full md:w-auto">
                    Apply
                </button>
                <a href="{{ route('shifts.index') }}" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-white/5 font-semibold rounded-xl text-xs transition duration-200 cursor-pointer w-full md:w-auto text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl overflow-hidden shadow-2xl">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-white/5 bg-slate-900/30 text-slate-400 text-xs font-bold uppercase tracking-wider">
                    <th class="px-6 py-4 w-10"><input type="checkbox" id="select-all" class="w-4 h-4 rounded border-slate-700 text-indigo-600 focus:ring-indigo-500 bg-slate-900 cursor-pointer"></th>
                    <th class="px-6 py-4">Shift Code</th>
                    <th class="px-6 py-4">Shift Name</th>
                    <th class="px-6 py-4">Time Window</th>
                    <th class="px-6 py-4">Grace Period</th>
                    <th class="px-6 py-4">Break Minutes</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5 text-sm text-slate-200">
                @forelse($shifts as $sf)
                    <tr class="hover:bg-white/2 transition duration-150">
                        <td class="px-6 py-4 w-10">
                            <input type="checkbox" class="row-checkbox w-4 h-4 rounded border-slate-700 text-indigo-600 focus:ring-indigo-500 bg-slate-900 cursor-pointer" value="{{ $sf->id }}">
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-indigo-400 font-bold">
                            {{ $sf->shift_code }}
                        </td>
                        <td class="px-6 py-4 font-semibold">
                            {{ $sf->shift_name }}
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-slate-300">
                            {{ date('h:i A', strtotime($sf->start_time)) }} - {{ date('h:i A', strtotime($sf->end_time)) }}
                        </td>
                        <td class="px-6 py-4 text-slate-300">
                            {{ $sf->grace_period_minutes }} mins
                        </td>
                        <td class="px-6 py-4 text-slate-300">
                            {{ $sf->break_minutes }} mins
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg uppercase tracking-wider border {{ $sf->status == 'active' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20' }}">
                                {{ $sf->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            @can('shift.edit')
                            <a href="{{ route('shifts.edit', $sf->id) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-xs font-semibold border border-white/5 transition duration-200 inline-block mr-2 cursor-pointer">
                                Edit
                            </a>
                            @endcan

                            @can('shift.delete')
                            <form action="{{ route('shifts.destroy', $sf->id) }}" method="POST" class="inline-block" onsubmit="confirmDelete(event, this, 'Are you sure you want to delete this shift?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 rounded-lg text-xs font-semibold border border-rose-500/10 transition duration-200 cursor-pointer">
                                    Delete
                                </button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-slate-500">
                            No shifts found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination links -->
    <div class="mt-6">
        {{ $shifts->links() }}
    </div>

</div>

<!-- Bulk Floating Action Bar -->
<div id="bulk-action-bar" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 bg-slate-900/90 border border-indigo-500/25 rounded-2xl py-3 px-6 shadow-2xl backdrop-blur-md flex items-center gap-6 transition-all duration-300 translate-y-24 opacity-0">
    <div class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse"></span>
        <span class="text-xs font-semibold text-slate-200" id="selected-count">0 selected</span>
    </div>
    <div class="h-4 w-px bg-white/10"></div>
    <div class="flex items-center gap-2">
        <button onclick="openBulkEditModal()" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl text-xs transition cursor-pointer">
            Bulk Edit
        </button>
        <button onclick="confirmBulkDelete()" class="px-3 py-1.5 bg-rose-600/10 hover:bg-rose-600/20 text-rose-400 hover:text-rose-300 rounded-xl text-xs font-semibold border border-rose-500/20 transition cursor-pointer">
            Bulk Delete
        </button>
    </div>
</div>

<!-- Bulk Edit Modal -->
<div id="bulkEditModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" onclick="closeBulkEditModal()"></div>
    <div class="bg-slate-900 border border-white/10 rounded-2xl p-6 max-w-sm w-full mx-4 relative z-10 shadow-2xl space-y-6">
        <div class="flex items-center justify-between border-b border-white/5 pb-4">
            <h3 class="text-lg font-bold text-white">Bulk Update Status</h3>
            <button onclick="closeBulkEditModal()" class="p-1.5 hover:bg-slate-800 rounded-lg text-slate-400 hover:text-white transition cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form id="bulk-edit-form" class="space-y-4">
            <div>
                <label for="bulk_status" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Status</label>
                <select name="status" id="bulk_status" class="w-full bg-slate-950 border border-slate-800 rounded-xl py-2 px-3 text-slate-200 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Keep unchanged</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-white/5 pt-4">
                <button type="button" onclick="closeBulkEditModal()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium text-xs transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-xs transition cursor-pointer">
                    Apply Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const selectAllCheckbox = document.getElementById('select-all');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const bulkActionBar = document.getElementById('bulk-action-bar');
        const selectedCountSpan = document.getElementById('selected-count');

        function updateBulkActionBar() {
            const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
            if (checkedCount > 0) {
                selectedCountSpan.innerText = `${checkedCount} selected`;
                bulkActionBar.classList.remove('translate-y-24', 'opacity-0');
                bulkActionBar.classList.add('translate-y-0', 'opacity-100');
            } else {
                bulkActionBar.classList.remove('translate-y-0', 'opacity-100');
                bulkActionBar.classList.add('translate-y-24', 'opacity-0');
            }
        }

        selectAllCheckbox.addEventListener('change', () => {
            rowCheckboxes.forEach(cb => {
                cb.checked = selectAllCheckbox.checked;
            });
            updateBulkActionBar();
        });

        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
                selectAllCheckbox.checked = checkedCount === rowCheckboxes.length;
                updateBulkActionBar();
            });
        });

        // Bulk Delete Action
        window.confirmBulkDelete = async () => {
            const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
            if (selectedIds.length === 0) return;

            if (confirm(`Are you sure you want to delete the ${selectedIds.length} selected shift(s)?`)) {
                try {
                    const response = await fetch('{{ route("shifts.bulk_destroy") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ ids: selectedIds })
                    });
                    const res = await response.json();
                    if (response.ok) {
                        alert(res.message);
                        window.location.reload();
                    } else {
                        alert(res.message || 'Error occurred while bulk deleting.');
                    }
                } catch (err) {
                    alert('Network error. Failed to bulk delete.');
                }
            }
        };

        // Modal triggers
        window.openBulkEditModal = () => {
            document.getElementById('bulkEditModal').classList.remove('hidden');
        };

        window.closeBulkEditModal = () => {
            document.getElementById('bulkEditModal').classList.add('hidden');
        };

        // Form submit
        const bulkEditForm = document.getElementById('bulk-edit-form');
        bulkEditForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
            if (selectedIds.length === 0) return;

            const formData = new FormData(bulkEditForm);
            const data = { ids: selectedIds };
            formData.forEach((value, key) => {
                if (value) {
                    data[key] = value;
                }
            });

            try {
                const response = await fetch('{{ route("shifts.bulk_update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                const res = await response.json();
                if (response.ok) {
                    alert(res.message);
                    window.location.reload();
                } else {
                    alert(res.message || 'Error occurred while bulk updating.');
                }
            } catch (err) {
                alert('Network error. Failed to bulk update.');
            }
        });
    });
</script>
@endsection
