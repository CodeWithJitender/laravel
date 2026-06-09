@extends('layouts.app')

@section('title', 'Leave Types Management')
@section('page_title', 'Leave Types')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Top Action Bar -->
    <div class="flex justify-between items-center">
        <div>
            <p class="text-sm text-slate-400">Manage master list of leave categories, paid/unpaid settings, and visual tags.</p>
        </div>
        
        <button onclick="toggleModal('createTypeModal')" 
           class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-xs transition duration-200 cursor-pointer flex items-center gap-2 shadow-lg shadow-indigo-500/10">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create Leave Type
        </button>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-2xl text-sm shadow-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-2xl text-sm shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Types Grid & Table -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Leave Types List -->
        <div class="lg:col-span-3 backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl overflow-hidden shadow-2xl">
            <div class="p-6 border-b border-white/5 bg-slate-900/30 flex justify-between items-center">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Active & Inactive Leave Types</h3>
            </div>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/5 bg-slate-900/10 text-slate-400 text-xs font-bold uppercase tracking-wider">
                        <th class="px-6 py-4">Visual</th>
                        <th class="px-6 py-4">Code</th>
                        <th class="px-6 py-4">Name</th>
                        <th class="px-6 py-4">Description</th>
                        <th class="px-6 py-4">Type Class</th>
                        <th class="px-6 py-4">Linked Policy</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-sm text-slate-200">
                    @forelse($leaveTypes as $type)
                        <tr class="hover:bg-white/2 transition duration-150">
                            <td class="px-6 py-4">
                                <div class="w-6 h-6 rounded-lg border border-white/10 shadow-inner" style="background-color: {{ $type->color }};"></div>
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-xs" style="color: {{ $type->color }};">
                                {{ $type->code }}
                            </td>
                            <td class="px-6 py-4 font-semibold">
                                {{ $type->name }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-400 max-w-xs truncate">
                                {{ $type->description ?? 'No description provided.' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 text-xs rounded-lg font-semibold border {{ $type->is_paid ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border-amber-500/20' }}">
                                    {{ $type->is_paid ? 'Paid' : 'Unpaid' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-300">
                                @if($type->policy)
                                    <span class="text-indigo-400 font-mono font-semibold">{{ number_format($type->policy->annual_allocation, 1) }} days/yr</span>
                                    <span class="block text-[10px] text-slate-500">{{ $type->policy->monthly_accrual ? 'Monthly Accrual' : 'Flat Allocation' }}</span>
                                @else
                                    <span class="text-slate-500">No policy defined</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg uppercase tracking-wider border {{ $type->status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-slate-500/10 text-slate-400 border-slate-500/20' }}">
                                    {{ $type->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button onclick="openEditModal({{ json_encode($type) }})"
                                   class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-xs font-semibold border border-white/5 transition duration-200 cursor-pointer">
                                    Edit
                                </button>
                                <form action="{{ route('leave-types.destroy', $type->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this leave type?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="px-2.5 py-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 rounded-lg text-xs font-semibold border border-rose-500/20 transition duration-200 cursor-pointer">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-slate-500">
                                No leave types configured yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div id="createTypeModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" onclick="toggleModal('createTypeModal')"></div>
    <div class="relative w-full max-w-lg backdrop-blur-xl bg-slate-900/90 border border-white/10 rounded-3xl p-8 shadow-2xl text-slate-100">
        <h3 class="text-lg font-bold mb-6">Create New Leave Type</h3>
        
        <form action="{{ route('leave-types.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Leave Type Name</label>
                <input type="text" name="name" required placeholder="e.g. Annual Leave, Medical Leave" 
                       class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Unique Code</label>
                    <input type="text" name="code" required placeholder="e.g. AL, SL, CL" 
                           class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100 uppercase">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Visual Color (Hex)</label>
                    <div class="flex gap-2">
                        <input type="color" name="color" value="#6366f1" required 
                               class="w-10 h-10 bg-slate-950 border border-white/10 rounded-xl cursor-pointer p-1">
                        <input type="text" id="colorHex" placeholder="#6366f1" readonly
                               class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none text-slate-400 font-mono">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Description</label>
                <textarea name="description" placeholder="Provide general guidelines regarding eligibility, notice periods, or usage constraints..." rows="3"
                          class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Leave Class</label>
                    <select name="is_paid" class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100">
                        <option value="1">Paid Leave</option>
                        <option value="0">Unpaid Leave</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                <button type="button" onclick="toggleModal('createTypeModal')"
                        class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
                    Save Leave Type
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editTypeModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" onclick="toggleModal('editTypeModal')"></div>
    <div class="relative w-full max-w-lg backdrop-blur-xl bg-slate-900/90 border border-white/10 rounded-3xl p-8 shadow-2xl text-slate-100">
        <h3 class="text-lg font-bold mb-6">Edit Leave Type</h3>
        
        <form id="editTypeForm" action="" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Leave Type Name</label>
                <input type="text" name="name" id="edit_name" required 
                       class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Unique Code</label>
                    <input type="text" name="code" id="edit_code" required 
                           class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100 uppercase">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Visual Color (Hex)</label>
                    <div class="flex gap-2">
                        <input type="color" name="color" id="edit_color" required 
                               class="w-10 h-10 bg-slate-950 border border-white/10 rounded-xl cursor-pointer p-1">
                        <input type="text" id="editColorHex" readonly
                               class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none text-slate-400 font-mono">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Description</label>
                <textarea name="description" id="edit_description" rows="3"
                          class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Leave Class</label>
                    <select name="is_paid" id="edit_is_paid" class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100">
                        <option value="1">Paid Leave</option>
                        <option value="0">Unpaid Leave</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Status</label>
                    <select name="status" id="edit_status" class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                <button type="button" onclick="toggleModal('editTypeModal')"
                        class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
                    Update Leave Type
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
        }
    }

    // Hex text listeners
    const colorPickers = [
        { picker: document.querySelector('input[name="color"]'), text: document.getElementById('colorHex') },
        { picker: document.getElementById('edit_color'), text: document.getElementById('editColorHex') }
    ];

    colorPickers.forEach(item => {
        if (item.picker && item.text) {
            item.text.value = item.picker.value;
            item.picker.addEventListener('input', (e) => {
                item.text.value = e.target.value;
            });
        }
    });

    function openEditModal(type) {
        document.getElementById('editTypeForm').action = `/leave-types/${type.id}`;
        document.getElementById('edit_name').value = type.name;
        document.getElementById('edit_code').value = type.code;
        document.getElementById('edit_color').value = type.color;
        document.getElementById('editColorHex').value = type.color;
        document.getElementById('edit_description').value = type.description || '';
        document.getElementById('edit_is_paid').value = type.is_paid ? "1" : "0";
        document.getElementById('edit_status').value = type.status;

        toggleModal('editTypeModal');
    }
</script>
@endsection
