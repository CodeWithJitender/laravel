@extends('layouts.app')

@section('title', 'Create Location')
@section('page_title', 'Create Location')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="backdrop-blur-md bg-white/5 border border-white/10 rounded-3xl p-8 shadow-2xl relative overflow-hidden mb-6">
        <div class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] rounded-full bg-indigo-500/5 blur-[80px] pointer-events-none"></div>

        <h2 class="text-xl font-semibold mb-6 text-slate-200">Location Specifications</h2>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('locations.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="location_code" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Location Code</label>
                    <input type="text" name="location_code" id="location_code" required value="{{ old('location_code') }}"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                        placeholder="e.g. DEL-01">
                </div>

                <div>
                    <label for="location_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Location Name</label>
                    <input type="text" name="location_name" id="location_name" required value="{{ old('location_name') }}"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                        placeholder="e.g. Delhi Office">
                </div>
            </div>

            <div>
                <label for="address" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Address</label>
                <input type="text" name="address" id="address" value="{{ old('address') }}"
                    class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                    placeholder="Street name, landmark...">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label for="city" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">City</label>
                    <input type="text" name="city" id="city" value="{{ old('city') }}"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                        placeholder="Delhi">
                </div>

                <div>
                    <label for="state" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">State</label>
                    <input type="text" name="state" id="state" value="{{ old('state') }}"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                        placeholder="Delhi NCR">
                </div>

                <div>
                    <label for="country" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Country</label>
                    <input type="text" name="country" id="country" value="{{ old('country') }}"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                        placeholder="India">
                </div>

                <div>
                    <label for="postal_code" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Postal Code</label>
                    <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code') }}"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm" 
                        placeholder="110001">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="timezone" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Timezone</label>
                    <select name="timezone" id="timezone" required
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm select-dark">
                        <option value="Asia/Kolkata" {{ old('timezone', 'Asia/Kolkata') == 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata (IST)</option>
                        <option value="America/New_York" {{ old('timezone') == 'America/New_York' ? 'selected' : '' }}>America/New_York (EST)</option>
                        <option value="Europe/London" {{ old('timezone') == 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT)</option>
                        <option value="Asia/Singapore" {{ old('timezone') == 'Asia/Singapore' ? 'selected' : '' }}>Asia/Singapore (SGT)</option>
                        <option value="UTC" {{ old('timezone') == 'UTC' ? 'selected' : '' }}>UTC</option>
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Status</label>
                    <select name="status" id="status" required
                        class="w-full bg-slate-900/60 border border-white/10 rounded-2xl py-3 px-4 text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 text-sm select-dark">
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                <a href="{{ route('locations.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold border border-white/5 transition duration-200">
                    Cancel
                </a>
                <button type="submit" 
                    class="px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold rounded-xl text-xs shadow-lg shadow-indigo-500/25 transition duration-200 transform hover:-translate-y-0.5 cursor-pointer">
                    Save Location
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
