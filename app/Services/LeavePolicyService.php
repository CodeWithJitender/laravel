<?php

namespace App\Services;

use App\Models\User;
use App\Models\LeavePolicy;

class LeavePolicyService extends BaseService
{
    public function isEligible(User $user, LeavePolicy $policy): bool
    {
        $detail = $user->employeeDetail;
        if (!$detail) {
            return false;
        }

        // Check if policy has rules
        $rules = $policy->rules;
        if ($rules->isEmpty()) {
            return true;
        }

        foreach ($rules as $rule) {
            $value = match ($rule->rule_type) {
                'gender' => strtolower($detail->gender ?? ''),
                'department' => $detail->department_id,
                'location' => $detail->location_id,
                'employment_type' => strtolower($detail->employment_type ?? ''),
                default => null,
            };

            if ($value === null) {
                return false;
            }

            $allowedValues = array_map(function ($val) {
                return is_string($val) ? strtolower($val) : $val;
            }, $rule->rule_values);

            if ($rule->rule_operator === 'in') {
                if (!in_array($value, $allowedValues)) {
                    return false;
                }
            } elseif ($rule->rule_operator === 'not_in') {
                if (in_array($value, $allowedValues)) {
                    return false;
                }
            }
        }

        return true;
    }
}
