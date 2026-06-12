@extends('layouts.app')

@section('title', 'Reports & Analytics')
@section('page_title', 'Reports Engine')

@section('content')
@php
    $departments = \App\Models\Department::where('status', 'active')->orderBy('department_name')->get();
    $locations = \App\Models\Location::where('status', 'active')->orderBy('location_name')->get();
@endphp

<div class="space-y-6 max-w-7xl mx-auto relative">
    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed top-6 right-6 z-50 flex flex-col gap-2 pointer-events-none"></div>

    <!-- Top Banner -->
    <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md relative overflow-hidden flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="absolute top-[-20%] right-[-10%] w-[250px] h-[250px] rounded-full bg-indigo-500/10 blur-[60px] pointer-events-none"></div>
        <div>
            <h2 class="text-xl font-bold text-white">Central Reporting Portal</h2>
            <p class="text-slate-400 text-sm mt-1">Select standard definitions, generate exports, preview database grids, and set email schedules.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="switchView('view-explorer')" class="view-btn px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-xs shadow transition cursor-pointer">
                Report Explorer
            </button>
            <button onclick="switchView('view-recent')" class="view-btn px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 font-medium text-xs transition cursor-pointer">
                Exports & Schedules
            </button>
        </div>
    </div>

    <!-- VIEW 1: Report Explorer -->
    <div id="view-explorer" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Panel: Available Reports List -->
        <div class="lg:col-span-1 space-y-6">
            @foreach($categories as $category)
                <div class="bg-white/5 border border-white/10 rounded-2xl p-5 backdrop-blur-md space-y-3">
                    <div class="border-b border-white/5 pb-2">
                        <span class="text-xs font-bold text-indigo-400 uppercase tracking-widest">{{ $category->name }}</span>
                        <p class="text-slate-400 text-xs mt-0.5">{{ $category->description }}</p>
                    </div>
                    
                    <div class="space-y-2">
                        @foreach($category->definitions as $def)
                            @php
                                $isFav = $favorites->contains('id', $def->id);
                            @endphp
                            <div id="def-item-{{ $def->uuid }}" onclick="selectReport('{{ $def->uuid }}', '{{ $def->report_name }}', '{{ $def->report_code }}', '{{ $def->description }}', {{ json_encode($def->filters) }}, {{ $isFav ? 'true' : 'false' }}, {{ $def->id }})"
                                 class="def-card group flex items-start justify-between p-3 bg-slate-900/50 hover:bg-white/5 border border-slate-800 hover:border-slate-700 rounded-xl transition duration-150 cursor-pointer">
                                <div class="min-w-0 pr-2">
                                    <span class="block text-sm font-semibold text-white group-hover:text-indigo-400 transition">{{ $def->report_name }}</span>
                                    <span class="block text-[11px] text-slate-500 mt-1 line-clamp-1">{{ $def->description }}</span>
                                </div>
                                <button onclick="event.stopPropagation(); toggleFavorite('{{ $def->uuid }}')" class="p-1 text-slate-500 hover:text-amber-400 transition cursor-pointer">
                                    <svg class="w-4 h-4 {{ $isFav ? 'fill-amber-400 text-amber-400' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="fav-star-{{ $def->uuid }}">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.371 1.24.588 1.81l-3.97 2.883a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.971-2.883a1 1 0 00-1.18 0l-3.97 2.883c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h4.906a1 1 0 00.951-.69l1.519-4.674z"></path>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Right Panel: Configurations, Filters, Preview, Actions -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Details & Filters Card -->
            <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md space-y-6 min-h-[300px] flex flex-col justify-between" id="report-details-card">
                <div id="no-report-selected" class="flex-grow flex flex-col items-center justify-center text-slate-500 text-sm py-12">
                    <svg class="w-12 h-12 text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H3a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Select a report template from the left directory to get started.
                </div>

                <div id="report-selected-content" class="hidden space-y-6 flex-grow">
                    <!-- Headings -->
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-white" id="selected-report-title">Report Title</h3>
                            <p class="text-slate-400 text-xs mt-1" id="selected-report-desc">Report Description</p>
                        </div>
                        <span id="selected-report-code-badge" class="px-2.5 py-1 text-[10px] font-bold text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 rounded-lg select-none uppercase">CODE</span>
                    </div>

                    <!-- Dynamic Filter Form -->
                    <div class="bg-slate-950/40 border border-white/5 rounded-xl p-4 space-y-4">
                        <span class="block text-xs font-bold text-slate-500 uppercase tracking-widest">Filter Parameters</span>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="filters-container">
                            <!-- Filters will be generated dynamically here -->
                            
                            <!-- Date Range fields -->
                            <div class="filter-field filter-date hidden">
                                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Start Date</label>
                                <input type="date" id="input-start-date" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-xs text-slate-100 focus:outline-none focus:border-indigo-500">
                            </div>
                            <div class="filter-field filter-date hidden">
                                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">End Date</label>
                                <input type="date" id="input-end-date" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-xs text-slate-100 focus:outline-none focus:border-indigo-500">
                            </div>

                            <!-- Department select field -->
                            <div class="filter-field filter-department hidden">
                                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Department</label>
                                <select id="input-department" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-xs text-slate-100 focus:outline-none focus:border-indigo-500">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Location select field -->
                            <div class="filter-field filter-location hidden">
                                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Location</label>
                                <select id="input-location" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-xs text-slate-100 focus:outline-none focus:border-indigo-500">
                                    <option value="">All Locations</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->id }}">{{ $loc->location_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Status select field (Schedules/Status) -->
                            <div class="filter-field filter-status-leave hidden">
                                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Status</label>
                                <select id="input-status-leave" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-xs text-slate-100 focus:outline-none focus:border-indigo-500">
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="filter-field filter-status-payroll hidden">
                                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Status</label>
                                <select id="input-status-payroll" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 text-xs text-slate-100 focus:outline-none focus:border-indigo-500">
                                    <option value="">All Statuses</option>
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Panel -->
                    <div class="border-t border-white/5 pt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <button onclick="runPreview()" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold text-xs transition cursor-pointer">
                            Run Data Preview
                        </button>
                        
                        <!-- Export Actions Form -->
                        <div class="flex items-center gap-1.5">
                            <select id="export-format" class="bg-slate-900 border border-slate-800 rounded-xl px-3 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-indigo-500 flex-grow">
                                <option value="pdf">PDF</option>
                                <option value="csv">CSV</option>
                                <option value="xlsx">Excel (XLSX)</option>
                            </select>
                            <button onclick="generateExport()" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow transition cursor-pointer">
                                Export
                            </button>
                        </div>

                        <!-- Trigger Schedule Modal -->
                        <button onclick="openScheduleModal()" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold text-xs transition cursor-pointer">
                            Schedule Delivery
                        </button>
                    </div>
                </div>
            </div>

            <!-- Preview Data Grid Card -->
            <div id="preview-grid-container" class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md space-y-4 hidden">
                <span class="block text-xs font-bold text-slate-500 uppercase tracking-widest border-b border-white/5 pb-2">Preview Grid (Limited to 10 Rows)</span>
                
                <div class="overflow-x-auto rounded-xl border border-white/5">
                    <table class="w-full text-left border-collapse text-xs" id="preview-table">
                        <thead>
                            <tr class="bg-white/[0.02] border-b border-white/10" id="preview-table-header"></tr>
                        </thead>
                        <tbody class="divide-y divide-white/5" id="preview-table-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- VIEW 2: Recent Exports & Schedules -->
    <div id="view-recent" class="space-y-6 hidden">
        <!-- Export Logs -->
        <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md space-y-4">
            <h3 class="text-lg font-bold text-white border-b border-white/5 pb-2">Recent Exports & Download Vault</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-white/10 bg-white/[0.02]">
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Report</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Format</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Date Triggered</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Download</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($recentExports as $exp)
                            <tr class="hover:bg-white/[0.01] transition">
                                <td class="px-6 py-4 font-semibold text-white">
                                    {{ $exp->reportDefinition?->report_name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 uppercase font-bold text-indigo-400">
                                    {{ $exp->export_format }}
                                </td>
                                <td class="px-6 py-4 text-slate-400">
                                    {{ $exp->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($exp->status === 'completed')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 uppercase">Completed</span>
                                    @elseif($exp->status === 'processing')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 border border-amber-500/20 text-amber-400 uppercase">Processing</span>
                                    @elseif($exp->status === 'failed')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 border border-rose-500/20 text-rose-400 uppercase">Failed</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 border border-slate-700 text-slate-400 uppercase">Queued</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($exp->status === 'completed' && $exp->file_path)
                                        <a href="/reports/exports/{{ $exp->uuid }}/download" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-500 text-white rounded font-medium transition cursor-pointer">
                                            Download
                                        </a>
                                    @else
                                        <span class="text-slate-500">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                    No completed report exports found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Scheduled Reports -->
        <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md space-y-4">
            <h3 class="text-lg font-bold text-white border-b border-white/5 pb-2">Active Email Schedules</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-white/10 bg-white/[0.02]">
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Report Name</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Recipient Email</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Frequency</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Schedule Time</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Next Run</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($activeSchedules as $sch)
                            <tr class="hover:bg-white/[0.01] transition">
                                <td class="px-6 py-4 font-semibold text-white">
                                    {{ $sch->reportDefinition?->report_name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-slate-300">
                                    {{ $sch->recipient_email }}
                                </td>
                                <td class="px-6 py-4 uppercase font-semibold text-indigo-400">
                                    {{ $sch->frequency }}
                                </td>
                                <td class="px-6 py-4 text-slate-400">
                                    {{ $sch->schedule_time }}
                                </td>
                                <td class="px-6 py-4 text-slate-400">
                                    {{ $sch->next_run ? \Carbon\Carbon::parse($sch->next_run)->format('M d, Y H:i') : 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                    No active schedules registered.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SCHEDULE DELIVERY MODAL -->
    <div id="schedule-modal" class="fixed inset-0 z-40 hidden flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm cursor-pointer" onclick="closeScheduleModal()"></div>
        <!-- Modal panel -->
        <div class="relative bg-slate-900 border border-white/10 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl z-50">
            <div class="flex justify-between items-center border-b border-white/5 pb-2">
                <h3 class="text-sm font-bold text-white">Create Report Schedule</h3>
                <button onclick="closeScheduleModal()" class="text-slate-400 hover:text-white cursor-pointer">&times;</button>
            </div>
            
            <form onsubmit="submitSchedule(event)" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Recipient Email *</label>
                    <input type="email" id="schedule-email" required placeholder="manager@company.com" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-indigo-500">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Frequency *</label>
                        <select id="schedule-frequency" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-indigo-500">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Time (HH:MM) *</label>
                        <input type="text" id="schedule-time" required placeholder="09:00" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Format *</label>
                    <select id="schedule-format" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-indigo-500">
                        <option value="pdf">PDF</option>
                        <option value="csv">CSV</option>
                        <option value="xlsx">Excel (XLSX)</option>
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeScheduleModal()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-750 text-slate-300 text-xs">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs shadow transition">
                        Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let currentUuid = '';
    let currentReportCode = '';
    let currentReportId = null;

    // View switcher
    function switchView(viewId) {
        document.getElementById('view-explorer').classList.add('hidden');
        document.getElementById('view-recent').classList.add('hidden');
        document.getElementById(viewId).classList.remove('hidden');

        // Toggle buttons colors
        const btns = document.getElementsByClassName('view-btn');
        for (let i = 0; i < btns.length; i++) {
            btns[i].classList.remove('bg-indigo-600', 'hover:bg-indigo-500', 'text-white', 'shadow');
            btns[i].classList.add('bg-slate-800', 'hover:bg-slate-700', 'text-slate-300', 'border', 'border-slate-700');
        }
        
        event.currentTarget.classList.remove('bg-slate-800', 'hover:bg-slate-700', 'text-slate-300', 'border', 'border-slate-700');
        event.currentTarget.classList.add('bg-indigo-600', 'hover:bg-indigo-500', 'text-white', 'shadow');
    }

    // Select a report and reveal dynamic filters
    function selectReport(uuid, name, code, desc, filters, isFav, reportId) {
        currentUuid = uuid;
        currentReportCode = code;
        currentReportId = reportId;

        // Reset cards highlighting
        document.querySelectorAll('.def-card').forEach(el => {
            el.classList.remove('border-indigo-500/50', 'bg-indigo-500/5');
            el.classList.add('border-slate-800', 'bg-slate-900/50');
        });

        // Highlight selected
        const selectedCard = document.getElementById(`def-item-${uuid}`);
        if (selectedCard) {
            selectedCard.classList.remove('border-slate-800', 'bg-slate-900/50');
            selectedCard.classList.add('border-indigo-500/50', 'bg-indigo-500/5');
        }

        // Show content panel
        document.getElementById('no-report-selected').classList.add('hidden');
        document.getElementById('report-selected-content').classList.remove('hidden');
        
        // Hide preview grid by default
        document.getElementById('preview-grid-container').classList.add('hidden');

        // Populate basic info
        document.getElementById('selected-report-title').innerText = name;
        document.getElementById('selected-report-desc').innerText = desc;
        document.getElementById('selected-report-code-badge').innerText = code;

        // Hide all filter fields first
        document.querySelectorAll('.filter-field').forEach(el => el.classList.add('hidden'));

        // Show fields based on report code (since we know the definitions from the database seeder)
        if (code === 'EMP_DIR') {
            document.querySelectorAll('.filter-department, .filter-location').forEach(el => el.classList.remove('hidden'));
        } else if (code === 'EMP_JOIN' || code === 'ATT_DAILY') {
            document.querySelectorAll('.filter-date').forEach(el => el.classList.remove('hidden'));
        } else if (code === 'LEAVE_REQUESTS') {
            document.querySelectorAll('.filter-status-leave').forEach(el => el.classList.remove('hidden'));
        } else if (code === 'PAYROLL_REGISTER') {
            document.querySelectorAll('.filter-status-payroll').forEach(el => el.classList.remove('hidden'));
        }
    }

    // Gather active filter parameters
    function gatherFilters() {
        const filters = {};
        if (currentReportCode === 'EMP_DIR') {
            const depVal = document.getElementById('input-department').value;
            const locVal = document.getElementById('input-location').value;
            if (depVal) filters['department_id'] = depVal;
            if (locVal) filters['location_id'] = locVal;
        } else if (currentReportCode === 'EMP_JOIN' || currentReportCode === 'ATT_DAILY') {
            const startVal = document.getElementById('input-start-date').value;
            const endVal = document.getElementById('input-end-date').value;
            if (startVal) filters['start_date'] = startVal;
            if (endVal) filters['end_date'] = endVal;
        } else if (currentReportCode === 'LEAVE_REQUESTS') {
            const statusVal = document.getElementById('input-status-leave').value;
            if (statusVal) filters['status'] = statusVal;
        } else if (currentReportCode === 'PAYROLL_REGISTER') {
            const statusVal = document.getElementById('input-status-payroll').value;
            if (statusVal) filters['status'] = statusVal;
        }
        return filters;
    }

    // Toast alert helper
    function showToast(type, message) {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `p-4 rounded-xl shadow-2xl flex items-center gap-3 border transition duration-300 text-sm font-semibold pointer-events-auto bg-slate-900/90 backdrop-blur-md ${
            type === 'success' ? 'border-emerald-500/30 text-emerald-400' : 'border-rose-500/30 text-rose-400'
        }`;
        toast.innerHTML = `
            <span class="w-2 h-2 rounded-full ${type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'} animate-ping"></span>
            <span>${message}</span>
        `;
        container.appendChild(toast);
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-y-[-10px]');
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    // Run preview
    async function runPreview() {
        if (!currentUuid) return;

        if (currentReportCode === 'EMP_JOIN' || currentReportCode === 'ATT_DAILY') {
            const startVal = document.getElementById('input-start-date').value;
            const endVal = document.getElementById('input-end-date').value;
            if (startVal && endVal) {
                const start = new Date(startVal);
                const end = new Date(endVal);
                if (end < start) {
                    showToast('error', 'End Date must be greater than or equal to Start Date.');
                    return;
                }
            }
        }

        const filters = gatherFilters();
        
        try {
            const response = await fetch(`/reports/${currentUuid}/preview?` + new URLSearchParams({
                filters: JSON.stringify(filters)
            }), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const res = await response.json();
            
            if (response.ok) {
                renderPreviewGrid(res.columns, res.data);
            } else {
                showToast('error', res.message || 'Failed to fetch report preview.');
            }
        } catch (err) {
            showToast('error', 'Network error loading report preview.');
        }
    }

    // Render columns and rows of preview data
    function renderPreviewGrid(columns, data) {
        const headerRow = document.getElementById('preview-table-header');
        const body = document.getElementById('preview-table-body');
        
        headerRow.innerHTML = '';
        body.innerHTML = '';

        // Display columns
        const colKeys = Object.keys(columns);
        colKeys.forEach(key => {
            const th = document.createElement('th');
            th.className = 'px-4 py-3 text-slate-400 uppercase tracking-wider font-semibold';
            th.innerText = columns[key];
            headerRow.appendChild(th);
        });

        // Display rows
        if (!data || data.length === 0) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.setAttribute('colspan', colKeys.length);
            td.className = 'px-4 py-6 text-center text-slate-500';
            td.innerText = 'No records matched filters.';
            tr.appendChild(td);
            body.appendChild(tr);
        } else {
            data.forEach(item => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-white/[0.01] border-b border-white/5';
                
                colKeys.forEach(key => {
                    const td = document.createElement('td');
                    td.className = 'px-4 py-3 text-slate-300 font-medium';
                    
                    // Resolve dot notation for relationships (e.g. employeeDetail.employee_code)
                    let val = item;
                    const parts = key.split('.');
                    for (let p of parts) {
                        val = val ? val[p] : '';
                    }
                    
                    td.innerText = val !== null && val !== undefined ? val : '-';
                    tr.appendChild(td);
                });
                body.appendChild(tr);
            });
        }

        // Show container
        document.getElementById('preview-grid-container').classList.remove('hidden');
    }

    // Toggle favorite report
    async function toggleFavorite(uuid) {
        try {
            const response = await fetch(`/reports/${uuid}/favorite`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            const res = await response.json();
            if (response.ok) {
                showToast('success', res.message);
                const star = document.getElementById(`fav-star-${uuid}`);
                if (star) {
                    if (res.status) {
                        star.classList.add('fill-amber-400', 'text-amber-400');
                    } else {
                        star.classList.remove('fill-amber-400', 'text-amber-400');
                    }
                }
            } else {
                showToast('error', res.message || 'Error updating favorites.');
            }
        } catch (err) {
            showToast('error', 'Network error.');
        }
    }

    // Queue Report generation
    async function generateExport() {
        if (!currentReportCode) return;

        if (currentReportCode === 'EMP_JOIN' || currentReportCode === 'ATT_DAILY') {
            const startVal = document.getElementById('input-start-date').value;
            const endVal = document.getElementById('input-end-date').value;
            if (startVal && endVal) {
                const start = new Date(startVal);
                const end = new Date(endVal);
                if (end < start) {
                    showToast('error', 'End Date must be greater than or equal to Start Date.');
                    return;
                }
            }
        }

        const format = document.getElementById('export-format').value;
        const filters = gatherFilters();

        try {
            const response = await fetch('/reports/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    report_code: currentReportCode,
                    filters: filters,
                    export_format: format
                })
            });
            
            const res = await response.json();
            
            if (response.ok) {
                showToast('success', res.message || 'Export queued successfully! Visit Exports tab.');
            } else {
                showToast('error', res.message || 'Error scheduling report export.');
            }
        } catch (err) {
            showToast('error', 'Network error generating report export.');
        }
    }

    // Schedules Modals
    function openScheduleModal() {
        document.getElementById('schedule-modal').classList.remove('hidden');
    }
    
    function closeScheduleModal() {
        document.getElementById('schedule-modal').classList.add('hidden');
    }

    async function submitSchedule(event) {
        event.preventDefault();
        if (!currentReportId) return;

        const email = document.getElementById('schedule-email').value;
        const frequency = document.getElementById('schedule-frequency').value;
        const time = document.getElementById('schedule-time').value;
        const format = document.getElementById('schedule-format').value;

        try {
            const response = await fetch('/reports/schedule', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    report_definition_id: currentReportId,
                    recipient_email: email,
                    frequency: frequency,
                    schedule_time: time,
                    export_format: format
                })
            });

            const res = await response.json();
            
            if (response.ok) {
                showToast('success', res.message || 'Report schedule created successfully!');
                closeScheduleModal();
            } else {
                showToast('error', res.message || 'Failed to schedule report.');
            }
        } catch (err) {
            showToast('error', 'Network error creating schedule.');
        }
    }
</script>
@endsection
