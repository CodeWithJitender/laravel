<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EmployeeDetail;
use App\Models\EmergencyContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = Auth::user();
        
        $employee = User::with([
            'employeeDetail.department',
            'employeeDetail.designation',
            'employeeDetail.location',
            'employeeDetail.shift',
            'employeeDetail.manager',
            'employeeDetail.emergencyContacts',
            'documents',
        ])->findOrFail($user->id);

        // Fetch active session logs for the Security tab
        $currentSessionId = $request->session()->getId();
        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->get()
            ->map(function ($session) use ($currentSessionId) {
                $userAgent = $session->user_agent;
                $browser = 'Unknown Browser';
                $platform = 'Unknown Platform';

                if (preg_match('/MSIE/i', $userAgent) && !preg_match('/Opera/i', $userAgent)) {
                    $browser = 'Internet Explorer';
                } elseif (preg_match('/Firefox/i', $userAgent)) {
                    $browser = 'Mozilla Firefox';
                } elseif (preg_match('/Chrome/i', $userAgent)) {
                    $browser = 'Google Chrome';
                } elseif (preg_match('/Safari/i', $userAgent)) {
                    $browser = 'Apple Safari';
                }

                if (preg_match('/windows|win32/i', $userAgent)) {
                    $platform = 'Windows';
                } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
                    $platform = 'Mac OS';
                } elseif (preg_match('/linux/i', $userAgent)) {
                    $platform = 'Linux';
                } elseif (preg_match('/iphone|ipad/i', $userAgent)) {
                    $platform = 'iOS';
                } elseif (preg_match('/android/i', $userAgent)) {
                    $platform = 'Android';
                }

                return [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'browser' => $browser,
                    'platform' => $platform,
                    'last_active' => date('Y-m-d H:i:s', $session->last_activity),
                    'is_current' => $session->id === $currentSessionId,
                ];
            });

        return view('profile.show', compact('employee', 'sessions'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $detail = $user->employeeDetail;

        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['required', 'string', 'in:male,female,other,Male,Female,Other'],
            'dob' => ['nullable', 'date', 'before:today'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_no' => ['nullable', 'string', 'max:50'],
            'pan_no' => ['nullable', 'string', 'max:20'],
        ]);

        $validated['gender'] = strtolower($validated['gender']);

        if (!$detail) {
            $detail = new EmployeeDetail();
            $detail->user_id = $user->id;
        }

        $detail->fill($validated);
        $detail->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Profile updated successfully.']);
        }

        return redirect()->route('profile.show')->with('success', 'Profile demographics updated successfully.');
    }

    public function storeEmergencyContact(Request $request)
    {
        $user = Auth::user();
        $detail = $user->employeeDetail;

        if (!$detail) {
            return redirect()->back()->with('error', 'Employee detail sheet must exist before listing contacts.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'relationship' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        // If marked primary, clear other primary contacts first
        if (!empty($validated['is_primary'])) {
            EmergencyContact::where('employee_detail_id', $detail->id)->update(['is_primary' => false]);
        }

        $validated['employee_detail_id'] = $detail->id;
        $validated['is_primary'] = !empty($validated['is_primary']);

        EmergencyContact::create($validated);

        return redirect()->route('profile.show', ['tab' => 'emergency'])->with('success', 'Emergency contact added successfully.');
    }

    public function updateEmergencyContact(Request $request, $id)
    {
        $user = Auth::user();
        $detail = $user->employeeDetail;
        
        $contact = EmergencyContact::where('employee_detail_id', $detail->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'relationship' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        if (!empty($validated['is_primary'])) {
            EmergencyContact::where('employee_detail_id', $detail->id)->update(['is_primary' => false]);
        }

        $validated['is_primary'] = !empty($validated['is_primary']);
        $contact->update($validated);

        return redirect()->route('profile.show', ['tab' => 'emergency'])->with('success', 'Emergency contact updated successfully.');
    }

    public function destroyEmergencyContact(Request $request, $id)
    {
        $user = Auth::user();
        $detail = $user->employeeDetail;

        $contact = EmergencyContact::where('employee_detail_id', $detail->id)->findOrFail($id);
        $contact->delete();

        return redirect()->route('profile.show', ['tab' => 'emergency'])->with('success', 'Emergency contact removed.');
    }
}
