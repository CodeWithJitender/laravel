@extends('layouts.app')

@section('title', 'Reporting Structure')
@section('page_title', 'Team Reporting Structure')

@section('content')
<div class="space-y-6">
    <!-- Action Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md">
        <div>
            <h2 class="text-xl font-bold text-white">Organization & Reporting Nodes</h2>
            <p class="text-slate-400 text-sm mt-1">Visualize organizational reporting lines, designations, and departments for your direct and indirect reports.</p>
        </div>
        <a href="/team-members" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-medium text-sm flex items-center gap-2 transition duration-200 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Directory
        </a>
    </div>

    <!-- Tree Container -->
    <div class="bg-white/5 border border-white/10 rounded-2xl p-8 backdrop-blur-md overflow-x-auto">
        <div class="min-w-[800px] flex flex-col items-center">
            
            <!-- Root Node (Manager) -->
            <div class="flex flex-col items-center">
                <div class="bg-indigo-600/10 border border-indigo-500/30 rounded-2xl p-5 w-72 text-center shadow-lg shadow-indigo-500/5 relative hover:border-indigo-500/60 transition duration-300">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center font-bold text-white text-md mx-auto shadow-md">
                        {{ substr($rootUser->name, 0, 2) }}
                    </div>
                    <h3 class="text-white font-semibold text-base mt-3">{{ $rootUser->name }}</h3>
                    <p class="text-xs text-indigo-400 font-medium mt-1">{{ $rootUser->employeeDetail?->designation?->designation_name ?? 'Manager' }}</p>
                    <span class="inline-block px-2.5 py-0.5 text-[10px] font-bold text-slate-400 bg-white/5 border border-white/10 rounded-full mt-2 uppercase">
                        {{ $rootUser->employeeDetail?->department?->department_name ?? 'Organization Root' }}
                    </span>
                </div>
                
                @if(count($hierarchyData) > 0)
                    <!-- Vertical Line down from Root -->
                    <div class="w-0.5 h-10 bg-white/10"></div>
                @endif
            </div>

            <!-- First Level Reports -->
            @if(count($hierarchyData) > 0)
                <div class="relative w-full">
                    <!-- Horizontal connection line -->
                    @if(count($hierarchyData) > 1)
                        <div class="absolute top-0 left-0 right-0 h-0.5 bg-white/10 mx-auto" style="width: calc(100% - {{ 100 / count($hierarchyData) }}%);"></div>
                    @endif

                    <div class="flex justify-between gap-6 w-full pt-0">
                        @foreach($hierarchyData as $index => $item)
                            @php
                                $reportUser = $item['user'];
                                $children = $item['children'];
                            @endphp
                            <div class="flex flex-col items-center flex-1">
                                <!-- Vertical line from horizontal path down to node -->
                                <div class="w-0.5 h-8 bg-white/10"></div>

                                <!-- Node Card -->
                                <div class="bg-white/5 border border-white/10 rounded-2xl p-4 w-64 text-center hover:bg-white/[0.08] hover:border-white/20 transition duration-300 relative group">
                                    <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-indigo-400 text-sm mx-auto">
                                        {{ substr($reportUser->name, 0, 2) }}
                                    </div>
                                    <h4 class="text-white font-semibold text-sm mt-2.5">{{ $reportUser->name }}</h4>
                                    <p class="text-[11px] text-slate-400 mt-0.5 font-medium">{{ $reportUser->employeeDetail?->designation?->designation_name ?? 'Direct Report' }}</p>
                                    <span class="inline-block px-2 py-0.5 text-[9px] text-slate-400 bg-white/5 border border-white/5 rounded-full mt-2">
                                        {{ $reportUser->employeeDetail?->department?->department_name ?? 'N/A' }}
                                    </span>
                                    
                                    <div class="mt-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <a href="/team-members/{{ $reportUser->id }}" class="text-[11px] text-indigo-400 font-semibold hover:text-indigo-300 underline">
                                            View Full Profile &rarr;
                                        </a>
                                    </div>
                                </div>

                                <!-- Lower Level Reports (Children of Direct Report) -->
                                @if(count($children) > 0)
                                    <!-- Line down from Node -->
                                    <div class="w-0.5 h-8 bg-white/10"></div>

                                    <!-- Horizontal line for children -->
                                    @if(count($children) > 1)
                                        <div class="w-full relative">
                                            <div class="absolute top-0 left-0 right-0 h-0.5 bg-white/10 mx-auto" style="width: calc(100% - {{ 100 / count($children) }}%);"></div>
                                        </div>
                                    @endif

                                    <div class="flex justify-center gap-4 pt-0">
                                        @foreach($children as $childIndex => $child)
                                            <div class="flex flex-col items-center">
                                                <!-- Line from child horizontal connector down to child node -->
                                                <div class="w-0.5 h-6 bg-white/10"></div>

                                                <!-- Child Card (More compact) -->
                                                <div class="bg-white/[0.03] border border-white/5 rounded-xl p-3 w-48 text-center hover:bg-white/5 transition duration-200">
                                                    <span class="block text-white font-semibold text-xs">{{ $child->name }}</span>
                                                    <span class="block text-[10px] text-slate-500 mt-0.5">{{ $child->employeeDetail?->designation?->designation_name ?? 'Staff' }}</span>
                                                    
                                                    <a href="/team-members/{{ $child->id }}" class="block text-[10px] text-indigo-500 font-medium hover:text-indigo-400 mt-2">
                                                        View Profile
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-12 text-slate-500">
                    <svg class="w-12 h-12 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <p class="text-sm">No direct reports found reporting to this node.</p>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
