@extends('layouts.app')

@section('title', 'Leave Policies Management')
@section('page_title', 'Leave Policies')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Top Action Bar -->
    <div class="flex justify-between items-center">
        <div>
            <p class="text-sm text-slate-400">Configure entitlements, notice limits, carry-forward bounds, and demographic filters for each leave category.</p>
        </div>
        
        @if($leaveTypes->isNotEmpty())
            <button onclick="toggleModal('createPolicyModal')" 
               class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-xs transition duration-200 cursor-pointer flex items-center gap-2 shadow-lg shadow-indigo-500/10">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Configure Policy
            </button>
        @else
            <button disabled 
               class="px-4 py-2.5 bg-slate-800 text-slate-500 font-semibold rounded-xl text-xs flex items-center gap-2 cursor-not-allowed border border-white/5"
               title="All active leave types already have policies configured.">
                All Types Configured
            </button>
        @endif
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

    <!-- Policies Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($policies as $policy)
            <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-6 shadow-xl hover:border-white/20 transition duration-200 flex flex-col justify-between relative overflow-hidden group">
                <div class="absolute top-[-30px] right-[-30px] w-24 h-24 rounded-full opacity-10" style="background-color: {{ $policy->leaveType->color }};"></div>
                
                <div>
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-4">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs rounded-xl font-bold border" 
                              style="background-color: {{ $policy->leaveType->color }}15; color: {{ $policy->leaveType->color }}; border-color: {{ $policy->leaveType->color }}30;">
                            {{ $policy->leaveType->name }} ({{ $policy->leaveType->code }})
                        </span>
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg uppercase tracking-wider border {{ $policy->status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-slate-500/10 text-slate-400 border-slate-500/20' }}">
                            {{ $policy->status }}
                        </span>
                    </div>

                    <!-- Main Stats Grid -->
                    <div class="grid grid-cols-2 gap-4 bg-slate-950/40 border border-white/5 rounded-2xl p-4 mb-4">
                        <div>
                            <span class="block text-[10px] text-slate-400 uppercase tracking-wider font-bold">Annual Limit</span>
                            <span class="text-xl font-bold font-mono text-slate-100">{{ number_format($policy->annual_allocation, 1) }} <span class="text-xs text-slate-400">days</span></span>
                        </div>
                        <div>
                            <span class="block text-[10px] text-slate-400 uppercase tracking-wider font-bold">Accrual Type</span>
                            <span class="text-xs font-semibold text-slate-200 block mt-1">{{ $policy->monthly_accrual ? 'Monthly Accrual' : 'Flat Allocation' }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] text-slate-400 uppercase tracking-wider font-bold">Carry Forward Limit</span>
                            <span class="text-sm font-bold font-mono text-indigo-400">{{ number_format($policy->carry_forward_limit, 1) }} days</span>
                        </div>
                        <div>
                            <span class="block text-[10px] text-slate-400 uppercase tracking-wider font-bold">Notice & Max Bounds</span>
                            <span class="text-[11px] text-slate-300 block mt-0.5">
                                Notice: {{ $policy->notice_period_days }} days<br>
                                Max Consec: {{ $policy->max_consecutive_days ?? 'Unlimited' }}
                            </span>
                        </div>
                    </div>

                    <!-- Demographic Rules / Restrictions -->
                    <div class="space-y-2 mb-6">
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Demographic Restrictions</h4>
                        @php
                            $genderRule = $policy->rules->where('rule_type', 'gender')->first();
                            $deptRule = $policy->rules->where('rule_type', 'department')->first();
                            $locRule = $policy->rules->where('rule_type', 'location')->first();
                        @endphp

                        <div class="space-y-1.5 text-xs">
                            <div class="flex items-center justify-between text-slate-400">
                                <span>Gender Constraint:</span>
                                @if($genderRule)
                                    <span class="text-slate-200 font-semibold">{{ implode(', ', $genderRule->rule_values) }}</span>
                                @else
                                    <span class="text-slate-500 italic">No restriction (All)</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between text-slate-400">
                                <span>Departments:</span>
                                @if($deptRule)
                                    @php
                                        $deptNames = \App\Models\Department::whereIn('id', $deptRule->rule_values)->pluck('name')->toArray();
                                    @endphp
                                    <span class="text-slate-200 font-semibold truncate max-w-[200px]" title="{{ implode(', ', $deptNames) }}">{{ implode(', ', $deptNames) }}</span>
                                @else
                                    <span class="text-slate-500 italic">No restriction (All)</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between text-slate-400">
                                <span>Locations:</span>
                                @if($locRule)
                                    @php
                                        $locNames = \App\Models\Location::whereIn('id', $locRule->rule_values)->pluck('name')->toArray();
                                    @endphp
                                    <span class="text-slate-200 font-semibold truncate max-w-[200px]" title="{{ implode(', ', $locNames) }}">{{ implode(', ', $locNames) }}</span>
                                @else
                                    <span class="text-slate-500 italic">No restriction (All)</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="border-t border-white/5 pt-4 flex justify-end">
                    <button onclick="openEditModal({{ json_encode($policy) }}, {{ json_encode($policy->rules) }})"
                            class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold border border-white/5 transition duration-200 cursor-pointer">
                        Configure Settings
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-10 text-center text-slate-500 text-sm">
                No leave policies configured yet. Select "Configure Policy" to set allocations and demographic rules for your leave types.
            </div>
        @endforelse
    </div>
</div>

<!-- Create Policy Modal -->
<div id="createPolicyModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" onclick="toggleModal('createPolicyModal')"></div>
    <div class="relative w-full max-w-2xl backdrop-blur-xl bg-slate-900/90 border border-white/10 rounded-3xl p-8 shadow-2xl text-slate-100 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-bold mb-6">Configure Leave Policy</h3>
        
        <form action="{{ route('leave-policies.store') }}" method="POST" class="space-y-4">
            @csrf
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Leave Category Type</label>
                    <select name="leave_type_id" required class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100">
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }} ({{ $type->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Policy Status</label>
                    <select name="status" class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Annual Entitlement (Days)</label>
                    <input type="number" step="0.5" name="annual_allocation" required placeholder="e.g. 15"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Allocation Mode</label>
                    <select name="monthly_accrual" class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100">
                        <option value="0">Flat Balance (Allocated full at start of year)</option>
                        <option value="1">Monthly Accrued (Earned proportionally end of month)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Max Carry Forward Limit</label>
                    <input type="number" step="0.5" name="carry_forward_limit" required value="0" placeholder="e.g. 5"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Required Notice Period (Days)</label>
                    <input type="number" name="notice_period_days" required value="0" placeholder="e.g. 3"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Max Consecutive Limit</label>
                    <input type="number" name="max_consecutive_days" placeholder="e.g. 5 (Leave empty for none)"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100">
                </div>
            </div>

            <!-- Demographic Restriction Rules -->
            <div class="border-t border-white/10 pt-4 space-y-4">
                <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Demographic Restrictions (Optional)</h4>
                
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Target Genders</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 text-sm text-slate-200">
                            <input type="checkbox" name="rule_gender_values[]" value="Male" class="rounded border-white/10 bg-slate-950 text-indigo-600 focus:ring-0 focus:ring-offset-0"> Male
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-200">
                            <input type="checkbox" name="rule_gender_values[]" value="Female" class="rounded border-white/10 bg-slate-950 text-indigo-600 focus:ring-0 focus:ring-offset-0"> Female
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-200">
                            <input type="checkbox" name="rule_gender_values[]" value="Other" class="rounded border-white/10 bg-slate-950 text-indigo-600 focus:ring-0 focus:ring-offset-0"> Other
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Target Departments</label>
                        <select name="rule_department_values[]" multiple class="w-full h-32 px-4 py-2 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100">
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        <span class="text-[10px] text-slate-500 mt-1 block">Hold Ctrl (Cmd) to select multiple departments. Leave unselected for all.</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Target Office Locations</label>
                        <select name="rule_location_values[]" multiple class="w-full h-32 px-4 py-2 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100">
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                        <span class="text-[10px] text-slate-500 mt-1 block">Hold Ctrl (Cmd) to select multiple locations. Leave unselected for all.</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                <button type="button" onclick="toggleModal('createPolicyModal')"
                        class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
                    Create Policy Configuration
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Policy Modal -->
<div id="editPolicyModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" onclick="toggleModal('editPolicyModal')"></div>
    <div class="relative w-full max-w-2xl backdrop-blur-xl bg-slate-900/90 border border-white/10 rounded-3xl p-8 shadow-2xl text-slate-100 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-bold mb-6">Configure Policy Settings</h3>
        
        <form id="editPolicyForm" action="" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Leave Category Type</label>
                    <input type="text" id="edit_leave_type_name" readonly
                           class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none text-slate-500 font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Policy Status</label>
                    <select name="status" id="edit_status" class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Annual Entitlement (Days)</label>
                    <input type="number" step="0.5" name="annual_allocation" id="edit_annual_allocation" required 
                           class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Allocation Mode</label>
                    <select name="monthly_accrual" id="edit_monthly_accrual" class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100">
                        <option value="0">Flat Balance (Allocated full at start of year)</option>
                        <option value="1">Monthly Accrued (Earned proportionally end of month)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Max Carry Forward Limit</label>
                    <input type="number" step="0.5" name="carry_forward_limit" id="edit_carry_forward_limit" required
                           class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Required Notice Period (Days)</label>
                    <input type="number" name="notice_period_days" id="edit_notice_period_days" required
                           class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Max Consecutive Limit</label>
                    <input type="number" name="max_consecutive_days" id="edit_max_consecutive_days"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100">
                </div>
            </div>

            <!-- Demographic Restriction Rules -->
            <div class="border-t border-white/10 pt-4 space-y-4">
                <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider">Demographic Restrictions (Optional)</h4>
                
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Target Genders</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 text-sm text-slate-200">
                            <input type="checkbox" name="rule_gender_values[]" id="edit_gender_male" value="Male" class="rounded border-white/10 bg-slate-950 text-indigo-600 focus:ring-0 focus:ring-offset-0"> Male
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-200">
                            <input type="checkbox" name="rule_gender_values[]" id="edit_gender_female" value="Female" class="rounded border-white/10 bg-slate-950 text-indigo-600 focus:ring-0 focus:ring-offset-0"> Female
                        </label>
                        <label class="flex items-center gap-2 text-sm text-slate-200">
                            <input type="checkbox" name="rule_gender_values[]" id="edit_gender_other" value="Other" class="rounded border-white/10 bg-slate-950 text-indigo-600 focus:ring-0 focus:ring-offset-0"> Other
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Target Departments</label>
                        <select name="rule_department_values[]" id="edit_rule_departments" multiple class="w-full h-32 px-4 py-2 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100">
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        <span class="text-[10px] text-slate-500 mt-1 block">Hold Ctrl (Cmd) to select multiple departments. Leave unselected for all.</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Target Office Locations</label>
                        <select name="rule_location_values[]" id="edit_rule_locations" multiple class="w-full h-32 px-4 py-2 bg-slate-950 border border-white/10 rounded-xl text-sm focus:outline-none focus:border-indigo-500 text-slate-100">
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                        <span class="text-[10px] text-slate-500 mt-1 block">Hold Ctrl (Cmd) to select multiple locations. Leave unselected for all.</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                <button type="button" onclick="toggleModal('editPolicyModal')"
                        class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-xs transition duration-200 cursor-pointer">
                    Update Policy Configuration
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

    function openEditModal(policy, rules) {
        document.getElementById('editPolicyForm').action = `/leave-policies/${policy.id}`;
        document.getElementById('edit_leave_type_name').value = `${policy.leave_type.name} (${policy.leave_type.code})`;
        document.getElementById('edit_annual_allocation').value = policy.annual_allocation;
        document.getElementById('edit_monthly_accrual').value = policy.monthly_accrual ? "1" : "0";
        document.getElementById('edit_carry_forward_limit').value = policy.carry_forward_limit;
        document.getElementById('edit_notice_period_days').value = policy.notice_period_days;
        document.getElementById('edit_max_consecutive_days').value = policy.max_consecutive_days || '';
        document.getElementById('edit_status').value = policy.status;

        // Reset inputs
        document.getElementById('edit_gender_male').checked = false;
        document.getElementById('edit_gender_female').checked = false;
        document.getElementById('edit_gender_other').checked = false;

        const deptSelect = document.getElementById('edit_rule_departments');
        for (let i = 0; i < deptSelect.options.length; i++) {
            deptSelect.options[i].selected = false;
        }

        const locSelect = document.getElementById('edit_rule_locations');
        for (let i = 0; i < locSelect.options.length; i++) {
            locSelect.options[i].selected = false;
        }

        // Apply rules values
        rules.forEach(rule => {
            if (rule.rule_type === 'gender') {
                rule.rule_values.forEach(val => {
                    if (val === 'Male') document.getElementById('edit_gender_male').checked = true;
                    if (val === 'Female') document.getElementById('edit_gender_female').checked = true;
                    if (val === 'Other') document.getElementById('edit_gender_other').checked = true;
                });
            } else if (rule.rule_type === 'department') {
                rule.rule_values.forEach(val => {
                    for (let i = 0; i < deptSelect.options.length; i++) {
                        if (deptSelect.options[i].value == val) {
                            deptSelect.options[i].selected = true;
                        }
                    }
                });
            } else if (rule.rule_type === 'location') {
                rule.rule_values.forEach(val => {
                    for (let i = 0; i < locSelect.options.length; i++) {
                        if (locSelect.options[i].value == val) {
                            locSelect.options[i].selected = true;
                        }
                    }
                });
            }
        });

        toggleModal('editPolicyModal');
    }
</script>
@endsection
