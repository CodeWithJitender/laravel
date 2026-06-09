<?php

namespace App\Services;

use App\Repositories\OfficeTimingRepositoryInterface;
use App\Models\OfficeTiming;

class OfficeTimingService extends BaseService
{
    protected $officeTimingRepo;

    public function __construct(OfficeTimingRepositoryInterface $officeTimingRepo)
    {
        $this->officeTimingRepo = $officeTimingRepo;
    }

    public function getDefault(): OfficeTiming
    {
        $timing = OfficeTiming::first();

        if (!$timing) {
            $timing = OfficeTiming::create([
                'name' => 'Default Office Timing',
                'working_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'minimum_hours' => 8.00,
                'half_day_hours' => 4.00,
                'weekly_off' => ['Saturday', 'Sunday'],
                'status' => 'active',
            ]);
        }

        return $timing;
    }

    public function updateDefault(array $data): OfficeTiming
    {
        return $this->transaction(function () use ($data) {
            $timing = $this->getDefault();
            $this->officeTimingRepo->updateModel($timing, $data);
            return $timing->fresh();
        });
    }
}
