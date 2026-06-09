@extends('layouts.app')

@section('title', 'Notification Center')
@section('page_title', 'Notification Center')

@section('content')
<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <p class="text-sm text-slate-400">View and manage all system alerts, approvals, and message logs.</p>
        </div>
        <div class="flex items-center gap-3">
            @if($unreadCount > 0)
                <form action="{{ route('notifications.read_all') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl transition cursor-pointer">
                        Mark All as Read
                    </button>
                </form>
            @endif
            @if(auth()->user()->hasRole('Admin'))
                <a href="{{ route('notification-templates.index') }}" class="px-4 py-2 text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl shadow-lg shadow-indigo-500/20 transition cursor-pointer">
                    Templates Panel
                </a>
                <a href="{{ route('notification-logs.index') }}" class="px-4 py-2 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl transition cursor-pointer">
                    Delivery Logs
                </a>
            @endif
        </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        @if($recipients->isEmpty())
            <div class="p-16 text-center">
                <div class="w-16 h-16 bg-slate-800/50 rounded-2xl flex items-center justify-center mx-auto text-slate-500 border border-slate-800 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-300">Your inbox is clear</h3>
                <p class="text-sm text-slate-500 mt-1">You don't have any notifications at the moment.</p>
            </div>
        @else
            <div class="divide-y divide-slate-800">
                @foreach($recipients as $recipient)
                    @php
                        $notification = $recipient->notification;
                        $priorityColor = 'slate';
                        if ($notification->priority === 'critical') $priorityColor = 'rose';
                        elseif ($notification->priority === 'high') $priorityColor = 'amber';
                        elseif ($notification->priority === 'medium') $priorityColor = 'indigo';

                        $isUnread = $recipient->status !== 'read' && $recipient->status !== 'archived';
                    @endphp
                    <div class="p-6 flex items-start gap-4 transition duration-150 hover:bg-slate-800/20 {{ $isUnread ? 'bg-indigo-600/[0.02]' : '' }}">
                        <!-- Priority Indicator -->
                        <span class="w-3 h-3 mt-1.5 rounded-full shrink-0 
                            @if($notification->priority === 'critical') bg-rose-500 shadow-lg shadow-rose-500/50 
                            @elseif($notification->priority === 'high') bg-amber-500 shadow-lg shadow-amber-500/50
                            @elseif($notification->priority === 'medium') bg-indigo-500 shadow-lg shadow-indigo-500/50
                            @else bg-slate-500 
                            @endif">
                        </span>

                        <!-- Notification details -->
                        <div class="flex-grow min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h4 class="text-sm font-bold text-slate-200">{{ $notification->title }}</h4>
                                <span class="px-2 py-0.5 text-[10px] font-semibold tracking-wide rounded-full uppercase border
                                    @if($notification->priority === 'critical') bg-rose-500/10 text-rose-400 border-rose-500/20
                                    @elseif($notification->priority === 'high') bg-amber-500/10 text-amber-400 border-amber-500/20
                                    @elseif($notification->priority === 'medium') bg-indigo-500/10 text-indigo-400 border-indigo-500/20
                                    @else bg-slate-800 text-slate-400 border-slate-700
                                    @endif">
                                    {{ $notification->priority }}
                                </span>
                                @if($isUnread)
                                    <span class="px-2 py-0.5 text-[10px] font-bold bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 rounded-full">
                                        Unread
                                    </span>
                                @endif
                                <span class="text-xs text-slate-500 ml-auto">
                                    {{ $recipient->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <p class="text-sm text-slate-300 mt-1 font-semibold">{{ $notification->subject }}</p>
                            <p class="text-sm text-slate-400 mt-2 whitespace-pre-line">{{ $notification->message }}</p>
                        </div>

                        <!-- Action buttons -->
                        <div class="flex items-center gap-2 ml-4 shrink-0">
                            @if($isUnread)
                                <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="p-2 bg-slate-800 hover:bg-indigo-600 hover:text-white border border-slate-700 hover:border-indigo-500 rounded-xl text-slate-400 transition cursor-pointer" title="Mark as Read">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                            @if($recipient->status !== 'archived')
                                <form action="{{ route('notifications.archive', $notification->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="p-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl text-slate-400 hover:text-white transition cursor-pointer" title="Archive">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this notification?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 bg-slate-800 hover:bg-rose-950 hover:text-rose-400 border border-slate-700 hover:border-rose-900 rounded-xl text-slate-400 transition cursor-pointer" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            
            @if($recipients->hasPages())
                <div class="p-6 border-t border-slate-800 bg-slate-900/50">
                    {{ $recipients->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
