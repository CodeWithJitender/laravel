<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\SystemSetting;
use App\Models\EmailSetting;
use App\Models\NotificationSetting;
use App\Models\AttendanceSetting;
use App\Models\LeaveSetting;
use App\Models\PayrollSetting;
use App\Models\SecuritySetting;
use App\Models\FileStorageSetting;
use App\Models\BackupSetting;
use App\Models\FeatureFlag;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    /**
     * Map setting groups to their corresponding Eloquent models.
     */
    protected array $groupModelMap = [
        'company' => CompanySetting::class,
        'system' => SystemSetting::class,
        'email' => EmailSetting::class,
        'notification' => NotificationSetting::class,
        'attendance' => AttendanceSetting::class,
        'leave' => LeaveSetting::class,
        'payroll' => PayrollSetting::class,
        'security' => SecuritySetting::class,
        'storage' => FileStorageSetting::class,
        'backup' => BackupSetting::class,
    ];

    /**
     * Get a specific setting value using dot notation (e.g. 'system.app_name').
     */
    public function get(string $key, $default = null)
    {
        if (strpos($key, '.') === false) {
            return $this->getGroup($key) ?? $default;
        }

        [$group, $attribute] = explode('.', $key, 2);

        $model = $this->getGroup($group);

        return $model ? ($model->{$attribute} ?? $default) : $default;
    }

    /**
     * Get the settings model for a group (e.g. 'system'), with caching.
     */
    public function getGroup(string $group)
    {
        if (!array_key_exists($group, $this->groupModelMap)) {
            return null;
        }

        $modelClass = $this->groupModelMap[$group];

        return Cache::rememberForever("settings.{$group}", function () use ($modelClass) {
            // Settings tables are single-row configurations, fetch the first record or create empty
            return $modelClass::first() ?: new $modelClass();
        });
    }

    /**
     * Update settings for a group and clear cache.
     */
    public function update(string $group, array $attributes): bool
    {
        if (!array_key_exists($group, $this->groupModelMap)) {
            throw new \Exception("Invalid settings group: {$group}");
        }

        $modelClass = $this->groupModelMap[$group];
        $model = $modelClass::first();

        if (!$model) {
            $model = new $modelClass();
        }

        $saved = $model->fill($attributes)->save();

        if ($saved) {
            Cache::forget("settings.{$group}");
            
            // Log setting change to Audit Logs
            activity()
                ->performedOn($model)
                ->withProperties(['group' => $group, 'attributes' => $attributes])
                ->log("Updated system settings for group: {$group}");
        }

        return $saved;
    }

    /**
     * Check if a feature flag is enabled.
     */
    public function isFeatureEnabled(string $flagKey, bool $default = true): bool
    {
        return Cache::rememberForever("feature_flag.{$flagKey}", function () use ($flagKey, $default) {
            $flag = FeatureFlag::where('flag_key', $flagKey)->first();
            return $flag ? (bool) $flag->flag_value : $default;
        });
    }

    /**
     * Set a feature flag value.
     */
    public function setFeatureEnabled(string $flagKey, bool $value, string $description = null): void
    {
        FeatureFlag::updateOrCreate(
            ['flag_key' => $flagKey],
            ['flag_value' => $value, 'description' => $description]
        );

        Cache::forget("feature_flag.{$flagKey}");

        activity()
            ->withProperties(['flag' => $flagKey, 'value' => $value])
            ->log("Feature flag changed: {$flagKey} to " . ($value ? 'enabled' : 'disabled'));
    }
}
