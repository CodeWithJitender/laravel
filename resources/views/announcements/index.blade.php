@extends('layouts.app')

@section('title', 'Corporate Announcements')
@section('page_title', 'Announcements')

@section('content')
<div class="space-y-6" x-data="{ openModal: false, editMode: false, currentAnnouncement: null, openCategoryModal: false }">
    <!-- Header Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <p class="text-sm text-slate-400">View official company announcements, policies updates, and division memos.</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()->hasAnyRole(['Admin', 'Manager']))
                <button @click="openCategoryModal = true" class="px-4 py-2 text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl shadow-lg transition cursor-pointer">
                    + Add Category
                </button>
                <button @click="openModal = true; editMode = false; currentAnnouncement = null" class="px-4 py-2 text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl shadow-lg shadow-indigo-500/20 transition cursor-pointer">
                    New Announcement
                </button>
            @endif
        </div>
    </div>

    <!-- Main Content Split -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Announcements Feed -->
        <div class="lg:col-span-2 space-y-4">
            @if($announcements->isEmpty())
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-16 text-center shadow-xl">
                    <div class="w-16 h-16 bg-slate-800/50 rounded-2xl flex items-center justify-center mx-auto text-slate-500 border border-slate-800 mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-300">No Announcements</h3>
                    <p class="text-sm text-slate-500 mt-1">There are no corporate announcements published for you at this time.</p>
                </div>
            @else
                @foreach($announcements as $announcement)
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl relative overflow-hidden transition duration-300 hover:border-slate-700">
                        <!-- Left colored accent border -->
                        <div class="absolute top-0 left-0 bottom-0 w-1 bg-indigo-500" style="background-color: {{ $announcement->category->color ?? '#6366f1' }}"></div>

                        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full border" 
                                          style="color: {{ $announcement->category->color }}; border-color: {{ $announcement->category->color }}20; background-color: {{ $announcement->category->color }}10">
                                        {{ $announcement->category->name }}
                                    </span>
                                    <span class="text-xs text-slate-500">
                                        Published {{ $announcement->publish_at ? $announcement->publish_at->diffForHumans() : $announcement->created_at->diffForHumans() }}
                                    </span>
                                    @if($announcement->status === 'draft')
                                        <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-full">Draft</span>
                                    @endif
                                </div>
                                <h3 class="text-base font-bold text-slate-100">{{ $announcement->title }}</h3>
                                <p class="text-sm text-slate-400">{{ $announcement->description }}</p>
                            </div>

                            @if(auth()->user()->hasAnyRole(['Admin', 'Manager']))
                                <div class="flex items-center gap-2 md:self-start">
                                    @if($announcement->status === 'draft')
                                        <form action="{{ route('announcements.publish', $announcement->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 text-[11px] font-bold bg-emerald-600/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-600 hover:text-white rounded-lg transition cursor-pointer">
                                                Publish
                                            </button>
                                        </form>
                                    @endif
                                    <button 
                                        @click="
                                            editMode = true; 
                                            currentAnnouncement = {{ json_encode($announcement) }};
                                            openModal = true;
                                        " 
                                        class="p-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 rounded-lg transition cursor-pointer"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                    </button>
                                    <form action="{{ route('announcements.destroy', $announcement->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this announcement?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 bg-slate-800 hover:bg-rose-950 text-slate-400 hover:text-rose-400 border border-slate-700 hover:border-rose-900 rounded-lg transition cursor-pointer" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        <!-- Rich Content Body -->
                        <div class="mt-4 pt-4 border-t border-slate-800 text-sm text-slate-300 leading-relaxed whitespace-pre-line">
                            {!! e($announcement->content) !!}
                        </div>

                        <!-- Read state and author info -->
                        <div class="mt-4 flex items-center justify-between text-xs text-slate-500">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 bg-slate-800 rounded-full flex items-center justify-center font-bold text-[10px] text-indigo-400 border border-slate-700">
                                    {{ substr($announcement->creator->name ?? 'HR', 0, 2) }}
                                </div>
                                <span class="font-semibold text-slate-400">By {{ $announcement->creator->name ?? 'HR Department' }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="capitalize">Target: {{ $announcement->audience_type }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if($announcements->hasPages())
                    <div class="mt-4 bg-slate-900 p-4 border border-slate-800 rounded-2xl">
                        {{ $announcements->links() }}
                    </div>
                @endif
            @endif
        </div>

        <!-- Sidebar Widgets -->
        <div class="space-y-6">
            <!-- Categories Widget -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4">Categories</h3>
                <div class="space-y-2">
                    @foreach($categories as $category)
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-800/20 border border-slate-800 hover:bg-slate-800/40 transition">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $category->color }}"></span>
                                <span class="text-sm font-medium text-slate-300">{{ $category->name }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Target Audience Guidelines -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-2">Audience Resolution</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Announcements can be scoped selectively. The engine will evaluate target criteria dynamically:
                </p>
                <ul class="text-xs text-slate-400 space-y-2 mt-3 list-disc pl-4">
                    <li><strong>All:</strong> Dispatched to every active employee.</li>
                    <li><strong>Department:</strong> Restricts visibility to selected business units.</li>
                    <li><strong>Location:</strong> Scopes messages to specific branch locations.</li>
                    <li><strong>Role:</strong> Targets specific roles (e.g. Managers only).</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- New / Edit Announcement Modal -->
    <div 
        x-show="openModal" 
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4"
        x-transition
        style="display: none;"
    >
        <div 
            @click.outside="openModal = false" 
            class="bg-slate-900 border border-slate-800 rounded-2xl max-w-xl w-full p-6 overflow-hidden shadow-2xl relative"
        >
            <div class="flex items-center justify-between pb-4 border-b border-slate-800 mb-4">
                <h3 class="text-lg font-bold text-slate-100" x-text="editMode ? 'Edit Announcement' : 'New Announcement'"></h3>
                <button @click="openModal = false" class="text-slate-400 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form :action="editMode ? `/announcements/${currentAnnouncement.id}` : '/announcements'" method="POST" class="space-y-4">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Title</label>
                    <input 
                        type="text" 
                        name="title" 
                        required 
                        :value="currentAnnouncement ? currentAnnouncement.title : ''"
                        class="w-full bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Short Summary / Description</label>
                    <input 
                        type="text" 
                        name="description" 
                        :value="currentAnnouncement ? currentAnnouncement.description : ''"
                        class="w-full bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition"
                    >
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Category</label>
                        <select 
                            name="category_id" 
                            required 
                            class="w-full bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition"
                        >
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" :selected="currentAnnouncement && currentAnnouncement.category_id == {{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Status</label>
                        <select 
                            name="status" 
                            required 
                            class="w-full bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition"
                        >
                            <option value="draft" :selected="currentAnnouncement && currentAnnouncement.status === 'draft'">Draft</option>
                            <option value="published" :selected="currentAnnouncement && currentAnnouncement.status === 'published'">Published</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4" x-data="{ targetType: 'all' }">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Audience Target</label>
                        <select 
                            name="audience_type" 
                            x-model="targetType"
                            required 
                            class="w-full bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition"
                        >
                            <option value="all">All Employees</option>
                            <option value="department">By Department</option>
                            <option value="location">By Location</option>
                            <option value="role">By Role</option>
                        </select>
                    </div>

                    <!-- Dynamic target values input based on audience selection -->
                    <div x-show="targetType !== 'all'">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Target Value IDs / Roles</label>
                        <!-- For Department -->
                        <div x-show="targetType === 'department'">
                            <select name="audience_values[]" class="w-full bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition" multiple>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->department_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- For Location -->
                        <div x-show="targetType === 'location'">
                            <select name="audience_values[]" class="w-full bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition" multiple>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->location_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- For Role -->
                        <div x-show="targetType === 'role'">
                            <select name="audience_values[]" class="w-full bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition" multiple>
                                <option value="Admin">Admin</option>
                                <option value="Manager">Manager</option>
                                <option value="Employee">Employee</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Content Body</label>
                    <textarea 
                        name="content" 
                        required 
                        rows="5" 
                        x-text="currentAnnouncement ? currentAnnouncement.content : ''"
                        class="w-full bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition resize-none"
                    ></textarea>
                </div>

                <div class="pt-4 border-t border-slate-800 flex justify-end gap-3">
                    <button type="button" @click="openModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 rounded-xl transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl shadow-lg shadow-indigo-500/20 transition cursor-pointer">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div 
        x-show="openCategoryModal" 
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4"
        x-transition
        style="display: none;"
    >
        <div 
            @click.outside="openCategoryModal = false" 
            class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 overflow-hidden shadow-2xl relative"
        >
            <div class="flex items-center justify-between pb-4 border-b border-slate-800 mb-4">
                <h3 class="text-lg font-bold text-slate-100">Add New Category</h3>
                <button @click="openCategoryModal = false" class="text-slate-400 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form action="{{ route('announcements.categories.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Category Name</label>
                    <input 
                        type="text" 
                        name="name" 
                        required 
                        placeholder="e.g. HR Policy, System Notice"
                        class="w-full bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-indigo-500 transition"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1.5">Color Tag</label>
                    <div class="flex items-center gap-3">
                        <input 
                            type="color" 
                            name="color" 
                            value="#6366f1"
                            class="w-10 h-10 bg-transparent border-0 cursor-pointer rounded-xl"
                        >
                        <span class="text-xs text-slate-400">Choose a distinct color for this category.</span>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-800 flex justify-end gap-3">
                    <button type="button" @click="openCategoryModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 rounded-xl transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl shadow-lg shadow-indigo-500/20 transition cursor-pointer">
                        Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Alpine.js inclusion (if not loaded yet) -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
