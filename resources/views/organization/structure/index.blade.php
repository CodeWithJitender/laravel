@extends('layouts.app')

@section('title', 'Organization Structure')
@section('page_title', 'Reporting Hierarchy Tree')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6">
        <p class="text-sm text-slate-400">Visual mapping of designation reporting lines, hierarchy levels, and active employee headcounts.</p>
    </div>

    <!-- Tree Panel Card -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-10 shadow-2xl relative overflow-hidden">
        <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] rounded-full bg-indigo-500/5 blur-[80px] pointer-events-none"></div>

        <!-- Scrollable Tree Container -->
        <div class="overflow-x-auto py-8">
            <div class="min-w-max flex flex-col items-center justify-center space-y-12">
                @forelse($tree as $rootNode)
                    @include('organization.structure.node', ['node' => $rootNode])
                @empty
                    <div class="text-center text-slate-500 py-10">
                        No designations or hierarchy definitions found in the system.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
