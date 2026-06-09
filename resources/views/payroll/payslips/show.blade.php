@extends('layouts.app')

@section('title', 'View Payslip')
@section('page_title', 'Payslip: ' . $payslip->reference_no)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header with Actions -->
    <div class="flex justify-between items-center bg-white/5 border border-white/10 rounded-3xl p-6 backdrop-blur-md">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('payslips.index') }}" class="hover:text-slate-200 transition">Payslips</a>
                <span>&rarr;</span>
                <span class="text-slate-300">View Payslip</span>
            </div>
            <h2 class="text-xl font-bold text-slate-200">
                Payslip Reference: {{ $payslip->reference_no }}
            </h2>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('payslips.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold border border-white/5 transition duration-200">
                Back to Archive
            </a>
            <a href="{{ route('payslips.download', $payslip->id) }}" target="_blank" class="px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-emerald-500/25 transition duration-200">
                Download Print / PDF
            </a>
        </div>
    </div>

    <!-- Embedded Print Layout Card -->
    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-2 shadow-2xl overflow-hidden">
        <iframe src="{{ route('payslips.download', $payslip->id) }}" class="w-full h-[800px] border-none rounded-2xl bg-white"></iframe>
    </div>
</div>
@endsection
