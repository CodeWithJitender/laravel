<div class="flex flex-col items-center">
    <!-- Designation Card -->
    <div class="px-5 py-4 bg-slate-900 border border-white/10 rounded-2xl shadow-xl hover:border-indigo-500/50 transition duration-200 w-64 text-center relative z-10">
        <div class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-1">Level {{ $node['level'] }}</div>
        <div class="text-sm font-semibold text-slate-200">{{ $node['designation_name'] }}</div>
        <div class="text-xs text-slate-500 font-mono mt-0.5">{{ $node['designation_code'] }}</div>
        
        @if(count($node['employees']) > 0)
            <div class="mt-3 pt-2.5 border-t border-white/5 space-y-1">
                @foreach($node['employees'] as $emp)
                    <div class="text-xs text-slate-300 flex items-center justify-center gap-1.5">
                        <div class="w-4 h-4 rounded-full bg-slate-800 text-[8px] font-bold flex items-center justify-center text-indigo-400 border border-slate-700">{{ substr($emp, 0, 1) }}</div>
                        <span>{{ $emp }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-[10px] text-slate-500 mt-2 font-medium italic">No active assignments</div>
        @endif
    </div>

    @if(count($node['children']) > 0)
        <!-- Line Connector Down -->
        <div class="w-0.5 h-8 bg-white/10 relative"></div>

        <!-- Children Wrapper -->
        <div class="flex gap-8 relative items-start">
            <!-- Line Connector Across -->
            @if(count($node['children']) > 1)
                <div class="absolute top-0 left-[128px] right-[128px] h-0.5 bg-white/10"></div>
            @endif
            
            @foreach($node['children'] as $child)
                <div class="flex flex-col items-center relative">
                    <!-- Line Connector Up to Horizontal Bar -->
                    <div class="w-0.5 h-4 bg-white/10"></div>
                    @include('organization.structure.node', ['node' => $child])
                </div>
            @endforeach
        </div>
    @endif
</div>
