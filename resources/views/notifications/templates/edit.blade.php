@extends('layouts.app')

@section('title', 'Edit Notification Template')
@section('page_title', 'Edit Template: ' . $template->name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('notification-templates.index') }}" class="px-4 py-2 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl transition cursor-pointer">
            Back to Templates
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Edit Form -->
        <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-6">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Template Details</h3>

            <form action="{{ route('notification-templates.update', $template->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Friendly Name</label>
                    <input 
                        type="text" 
                        name="name" 
                        required 
                        value="{{ old('name', $template->name) }}"
                        class="w-full bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition"
                    >
                    @error('name')
                        <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Subject Blueprint</label>
                    <input 
                        type="text" 
                        name="subject" 
                        required 
                        value="{{ old('subject', $template->subject) }}"
                        class="w-full bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition"
                    >
                    @error('subject')
                        <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Message Content Blueprint</label>
                    <textarea 
                        name="content" 
                        required 
                        rows="8" 
                        class="w-full bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition resize-none"
                    >{{ old('content', $template->content) }}</textarea>
                    @error('content')
                        <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-6 pt-2">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Delivery Channels</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2.5 cursor-pointer text-sm text-slate-300">
                                <input 
                                    type="checkbox" 
                                    name="channels[]" 
                                    value="in_app" 
                                    {{ in_array('in_app', old('channels', $template->channels)) ? 'checked' : '' }}
                                    class="rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-0"
                                >
                                In-App Notification Center
                            </label>
                            <label class="flex items-center gap-2.5 cursor-pointer text-sm text-slate-300">
                                <input 
                                    type="checkbox" 
                                    name="channels[]" 
                                    value="email" 
                                    {{ in_array('email', old('channels', $template->channels)) ? 'checked' : '' }}
                                    class="rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-0"
                                >
                                Outbound Email Alert
                            </label>
                        </div>
                        @error('channels')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Template Status</label>
                        <select 
                            name="status" 
                            required 
                            class="w-full bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition"
                        >
                            <option value="active" {{ old('status', $template->status) === 'active' ? 'selected' : '' }}>Active (Will dispatch on event triggers)</option>
                            <option value="inactive" {{ old('status', $template->status) === 'inactive' ? 'selected' : '' }}>Inactive (Disabled)</option>
                        </select>
                        @error('status')
                            <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-800 flex justify-end gap-3">
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl shadow-lg shadow-indigo-500/20 transition cursor-pointer">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Placeholder Helper Sidebar -->
        <div class="space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-2">Placeholder Helper</h3>
                <p class="text-xs text-slate-500 leading-relaxed mb-4">
                    Copy and paste placeholders below. The engine will dynamically compile user and event context attributes during dispatch:
                </p>
                <div class="space-y-3">
                    <div>
                        <span class="block text-xs font-bold text-slate-400 mb-1">Common Details</span>
                        <div class="space-y-1">
                            <code class="block text-xs text-indigo-400 bg-slate-800/80 p-1.5 rounded select-all cursor-pointer font-mono border border-slate-700/50">&#123;&#123;employee_name&#125;&#125;</code>
                            <code class="block text-xs text-indigo-400 bg-slate-800/80 p-1.5 rounded select-all cursor-pointer font-mono border border-slate-700/50">&#123;&#123;employee_email&#125;&#125;</code>
                        </div>
                    </div>

                    <div>
                        <span class="block text-xs font-bold text-slate-400 mb-1">Leave Requests Context</span>
                        <div class="space-y-1">
                            <code class="block text-xs text-indigo-400 bg-slate-800/80 p-1.5 rounded select-all cursor-pointer font-mono border border-slate-700/50">&#123;&#123;leave_type&#125;&#125;</code>
                            <code class="block text-xs text-indigo-400 bg-slate-800/80 p-1.5 rounded select-all cursor-pointer font-mono border border-slate-700/50">&#123;&#123;start_date&#125;&#125;</code>
                            <code class="block text-xs text-indigo-400 bg-slate-800/80 p-1.5 rounded select-all cursor-pointer font-mono border border-slate-700/50">&#123;&#123;end_date&#125;&#125;</code>
                            <code class="block text-xs text-indigo-400 bg-slate-800/80 p-1.5 rounded select-all cursor-pointer font-mono border border-slate-700/50">&#123;&#123;total_days&#125;&#125;</code>
                            <code class="block text-xs text-indigo-400 bg-slate-800/80 p-1.5 rounded select-all cursor-pointer font-mono border border-slate-700/50">&#123;&#123;reason&#125;&#125;</code>
                            <code class="block text-xs text-indigo-400 bg-slate-800/80 p-1.5 rounded select-all cursor-pointer font-mono border border-slate-700/50">&#123;&#123;approver_name&#125;&#125;</code>
                            <code class="block text-xs text-indigo-400 bg-slate-800/80 p-1.5 rounded select-all cursor-pointer font-mono border border-slate-700/50">&#123;&#123;remarks&#125;&#125;</code>
                        </div>
                    </div>

                    <div>
                        <span class="block text-xs font-bold text-slate-400 mb-1">Attendance Correction Context</span>
                        <div class="space-y-1">
                            <code class="block text-xs text-indigo-400 bg-slate-800/80 p-1.5 rounded select-all cursor-pointer font-mono border border-slate-700/50">&#123;&#123;requested_date&#125;&#125;</code>
                            <code class="block text-xs text-indigo-400 bg-slate-800/80 p-1.5 rounded select-all cursor-pointer font-mono border border-slate-700/50">&#123;&#123;requested_clock_in&#125;&#125;</code>
                            <code class="block text-xs text-indigo-400 bg-slate-800/80 p-1.5 rounded select-all cursor-pointer font-mono border border-slate-700/50">&#123;&#123;requested_clock_out&#125;&#125;</code>
                            <code class="block text-xs text-indigo-400 bg-slate-800/80 p-1.5 rounded select-all cursor-pointer font-mono border border-slate-700/50">&#123;&#123;approver_name&#125;&#125;</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
