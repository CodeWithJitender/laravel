@extends('layouts.app')

@section('title', 'Notification Delivery Audit Trail')
@section('page_title', 'Notification Logs')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <p class="text-sm text-slate-400">View audit logs of notification delivery states, failures, device details, and outbound delivery channels.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('notification-templates.index') }}" class="px-4 py-2 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl transition cursor-pointer">
                Templates Panel
            </a>
            <a href="{{ route('notifications.index') }}" class="px-4 py-2 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl transition cursor-pointer">
                Back to Inbox
            </a>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        @if($logs->isEmpty())
            <div class="p-16 text-center">
                <div class="w-16 h-16 bg-slate-800/50 rounded-2xl flex items-center justify-center mx-auto text-slate-500 border border-slate-800 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-300">No logs found</h3>
                <p class="text-sm text-slate-500 mt-1">There are no notification delivery records logged yet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-[11px] font-bold text-slate-400 uppercase bg-slate-900/50">
                            <th class="p-4 pl-6">Timestamp</th>
                            <th class="p-4">Recipient</th>
                            <th class="p-4">Notification / Subject</th>
                            <th class="p-4">Channel</th>
                            <th class="p-4">Delivery Status</th>
                            <th class="p-4 pr-6">Metadata / Errors</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50 text-sm">
                        @foreach($logs as $log)
                            <tr class="hover:bg-slate-800/10 transition">
                                <td class="p-4 pl-6 text-xs text-slate-500 font-mono">
                                    {{ $log->created_at->toDateTimeString() }}
                                </td>
                                <td class="p-4">
                                    <div class="font-semibold text-slate-200">{{ $log->employee->name }}</div>
                                    <div class="text-xs text-slate-500 font-mono">{{ $log->employee->email }}</div>
                                </td>
                                <td class="p-4">
                                    <span class="font-bold text-slate-300 block text-xs">{{ $log->notification->title ?? 'System Custom Notification' }}</span>
                                    <span class="text-xs text-slate-400 truncate max-w-xs block">{{ $log->notification->subject ?? '-' }}</span>
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded border bg-indigo-600/10 text-indigo-400 border-indigo-500/20">
                                        {{ $log->channel }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    @if($log->status === 'sent')
                                        <span class="px-2 py-0.5 text-xs font-semibold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 rounded-full">
                                            Success
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs font-semibold text-rose-400 bg-rose-500/10 border border-rose-500/20 rounded-full">
                                            Failed
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 pr-6 text-xs max-w-xs">
                                    @if($log->error_message)
                                        <p class="text-rose-400 font-mono bg-rose-500/[0.03] p-1.5 rounded border border-rose-500/20 whitespace-normal break-words">{{ $log->error_message }}</p>
                                    @else
                                        <div class="text-slate-500 space-y-0.5">
                                            <p class="truncate" title="User Agent">UA: {{ $log->device_info ?? '-' }}</p>
                                            <p>IP: <code class="bg-slate-800 px-1 rounded text-slate-400">{{ $log->ip_address ?? '-' }}</code></p>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="p-6 border-t border-slate-800 bg-slate-900/50">
                    {{ $logs->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
