<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest;
use App\Support\Facades\Settings;
use App\Models\FeatureFlag;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display settings dashboard view.
     */
    public function index(Request $request)
    {
        if (!$request->user()->hasRole('Admin')) {
            abort(403);
        }

        $company = Settings::getGroup('company');
        $system = Settings::getGroup('system');
        $email = Settings::getGroup('email');
        $notification = Settings::getGroup('notification');
        $attendance = Settings::getGroup('attendance');
        $leave = Settings::getGroup('leave');
        $payroll = Settings::getGroup('payroll');
        $security = Settings::getGroup('security');
        $storage = Settings::getGroup('storage');
        $backup = Settings::getGroup('backup');
        $featureFlags = FeatureFlag::all();

        return view('settings.index', compact(
            'company', 'system', 'email', 'notification', 'attendance', 
            'leave', 'payroll', 'security', 'storage', 'backup', 'featureFlags'
        ));
    }

    /**
     * Get settings for a specific group.
     */
    public function getSettings(Request $request, string $group)
    {
        $settings = Settings::getGroup($group);

        if (!$settings) {
            return response()->json(['message' => 'Settings group not found.'], 404);
        }

        return response()->json($settings);
    }

    /**
     * Update settings for a specific group.
     */
    public function updateSettings(UpdateSettingsRequest $request, string $group)
    {
        $validated = $request->validated();

        if ($group === 'company' && $request->hasFile('company_logo')) {
            $file = $request->file('company_logo');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $validated['company_logo'] = '/images/' . $filename;
        }

        try {
            $success = Settings::update($group, $validated);

            if ($success) {
                return response()->json([
                    'message' => "Settings for group '{$group}' updated successfully.",
                    'settings' => Settings::getGroup($group),
                ]);
            }

            return response()->json(['message' => 'Failed to update settings.'], 400);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Get all feature flags.
     */
    public function getFeatureFlags(Request $request)
    {
        if (!$request->user()->hasRole('Admin')) {
            abort(403);
        }

        $flags = FeatureFlag::all();

        return response()->json($flags);
    }

    /**
     * Update a feature flag value.
     */
    public function updateFeatureFlag(Request $request)
    {
        if (!$request->user()->hasRole('Admin')) {
            abort(403);
        }

        $request->validate([
            'flag_key' => 'required|string|exists:feature_flags,flag_key',
            'flag_value' => 'required|boolean',
            'description' => 'nullable|string|max:255',
        ]);

        $key = $request->input('flag_key');
        $value = $request->input('flag_value');
        $desc = $request->input('description');

        Settings::setFeatureEnabled($key, $value, $desc);

        return response()->json([
            'message' => "Feature flag '{$key}' updated successfully.",
            'flag' => FeatureFlag::where('flag_key', $key)->first(),
        ]);
    }
}
