@extends('layouts.app')

@section('title', 'Notification Templates')
@section('page_title', 'Notification Templates')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <p class="text-sm text-slate-400">Manage subject lines, message contents, and toggle channels for automated system alerts.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('notifications.index') }}" class="px-4 py-2 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl transition cursor-pointer">
                Back to Inbox
            </a>
            <a href="{{ route('notification-logs.index') }}" class="px-4 py-2 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl transition cursor-pointer">
                Delivery Logs
            </a>
        </div>
    </div>

    <!-- Templates Grid -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-800 text-[11px] font-bold text-slate-400 uppercase bg-slate-900/50">
                        <th class="p-4 pl-6">Template Name / Key</th>
                        <th class="p-4">Subject Blueprint</th>
                        <th class="p-4">Default Channels</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 pr-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    @foreach($templates as $template)
                        <tr class="hover:bg-slate-800/10 transition">
                            <td class="p-4 pl-6">
                                <span class="font-bold text-slate-200 block">{{ $template->name }}</span>
                                <code class="text-[10px] text-slate-500 bg-slate-800 px-1.5 py-0.5 rounded border border-slate-700/50">{{ $template->key }}</code>
                            </td>
                            <td class="p-4 text-sm text-slate-300">
                                {{ $template->subject }}
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-1.5">
                                    @foreach($template->channels as $channel)
                                        <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded bg-indigo-600/10 text-indigo-400 border border-indigo-500/20">
                                            {{ $channel }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full 
                                    {{ $template->status === 'active' ? 'bg-emerald-600/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-800 text-slate-500 border border-slate-700' }}">
                                    {{ ucfirst($template->status) }}
                                </span>
                            </td>
                            <td class="p-4 pr-6 text-right">
                                <a href="{{ route('notification-templates.edit', $template->id) }}" class="px-3 py-1.5 text-xs font-bold bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 hover:text-white rounded-lg transition inline-block cursor-pointer">
                                    Edit Template
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
