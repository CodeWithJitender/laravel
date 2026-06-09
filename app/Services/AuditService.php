<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log a data change audit entry.
     */
    public function logChange(?User $user, string $module, string $action, Model $model, ?array $oldValues, ?array $newValues): void
    {
        $userAgent = Request::header('User-Agent', '');
        
        AuditLog::create([
            'user_id' => $user ? $user->id : null,
            'module' => $module,
            'action' => $action,
            'record_type' => get_class($model),
            'record_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'browser' => substr($userAgent, 0, 150),
            'device' => $this->detectDevice($userAgent),
        ]);
    }

    /**
     * Basic user-agent device detector.
     */
    protected function detectDevice(string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'System';
        }
        
        $userAgentLower = strtolower($userAgent);
        
        if (strpos($userAgentLower, 'mobile') !== false || strpos($userAgentLower, 'android') !== false || strpos($userAgentLower, 'iphone') !== false) {
            return 'Mobile';
        }
        
        if (strpos($userAgentLower, 'tablet') !== false || strpos($userAgentLower, 'ipad') !== false) {
            return 'Tablet';
        }
        
        return 'Desktop';
    }
}
