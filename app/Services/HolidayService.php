<?php

namespace App\Services;

use App\Models\Holiday;
use App\Models\HolidayReminder;
use App\Models\User;
use App\Repositories\HolidayRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class HolidayService extends BaseService
{
    protected $holidayRepo;

    public function __construct(HolidayRepositoryInterface $holidayRepo)
    {
        $this->holidayRepo = $holidayRepo;
    }

    /**
     * Create a new holiday.
     */
    public function createHoliday(array $data, User $actor = null)
    {
        return $this->transaction(function () use ($data, $actor) {
            $holidayDate = Carbon::parse($data['holiday_date']);

            // Create core holiday
            $holiday = Holiday::create([
                'holiday_name' => $data['holiday_name'],
                'holiday_code' => $data['holiday_code'],
                'description' => $data['description'] ?? null,
                'holiday_date' => $holidayDate->toDateString(),
                'holiday_type_id' => $data['holiday_type_id'],
                'is_paid' => isset($data['is_paid']) ? (bool) $data['is_paid'] : true,
                'status' => $data['status'] ?? 'draft',
                'created_by' => $actor ? $actor->id : null,
                'publish_at' => ($data['status'] ?? 'draft') === 'published' ? now() : null,
            ]);

            // Map locations many-to-many
            if (!empty($data['location_ids'])) {
                $holiday->locations()->sync($data['location_ids']);
            }

            // If published, schedule reminders immediately
            if ($holiday->status === 'published') {
                $this->scheduleReminders($holiday);
                event(new \App\Events\HolidayPublished($holiday));
            } else {
                event(new \App\Events\HolidayCreated($holiday));
            }

            return $holiday;
        });
    }

    /**
     * Update an existing holiday.
     */
    public function updateHoliday(int $id, array $data)
    {
        return $this->transaction(function () use ($id, $data) {
            $holiday = Holiday::findOrFail($id);
            $oldStatus = $holiday->status;

            $holidayDate = isset($data['holiday_date']) ? Carbon::parse($data['holiday_date']) : Carbon::parse($holiday->holiday_date);

            $holiday->update([
                'holiday_name' => $data['holiday_name'] ?? $holiday->holiday_name,
                'holiday_code' => $data['holiday_code'] ?? $holiday->holiday_code,
                'description' => isset($data['description']) ? $data['description'] : $holiday->description,
                'holiday_date' => $holidayDate->toDateString(),
                'holiday_type_id' => $data['holiday_type_id'] ?? $holiday->holiday_type_id,
                'is_paid' => isset($data['is_paid']) ? (bool)$data['is_paid'] : $holiday->is_paid,
                'status' => $data['status'] ?? $holiday->status,
                'publish_at' => ($data['status'] ?? $holiday->status) === 'published' && !$holiday->publish_at ? now() : $holiday->publish_at,
            ]);

            if (isset($data['location_ids'])) {
                $holiday->locations()->sync($data['location_ids']);
            }

            // Status transitions
            if ($oldStatus !== 'published' && $holiday->status === 'published') {
                $this->scheduleReminders($holiday);
                event(new \App\Events\HolidayPublished($holiday));
            } else {
                event(new \App\Events\HolidayUpdated($holiday));
            }

            return $holiday;
        });
    }

    /**
     * Delete a holiday.
     */
    public function deleteHoliday(int $id)
    {
        return $this->transaction(function () use ($id) {
            $holiday = Holiday::findOrFail($id);
            
            // Delete reminders
            HolidayReminder::where('holiday_id', $holiday->id)->delete();

            event(new \App\Events\HolidayDeleted($holiday));

            $holiday->delete();

            return true;
        });
    }

    /**
     * Publish a holiday.
     */
    public function publishHoliday(int $id)
    {
        return $this->transaction(function () use ($id) {
            $holiday = Holiday::findOrFail($id);

            if ($holiday->status !== 'published') {
                $holiday->update([
                    'status' => 'published',
                    'publish_at' => now(),
                ]);

                $this->scheduleReminders($holiday);
                event(new \App\Events\HolidayPublished($holiday));
            }

            return $holiday;
        });
    }

    /**
     * Check if a specific date is a holiday for an employee based on location.
     */
    public function isHolidayForUserLocation(User $user, $date): bool
    {
        $dateStr = Carbon::parse($date)->toDateString();
        $locationId = $user->employeeDetail ? $user->employeeDetail->location_id : null;

        if (!$locationId) {
            // Check national/company-wide holidays
            return Holiday::where('status', 'published')
                ->whereDate('holiday_date', $dateStr)
                ->whereDoesntHave('locations')
                ->exists();
        }

        return Holiday::where('status', 'published')
            ->whereDate('holiday_date', $dateStr)
            ->where(function ($query) use ($locationId) {
                $query->whereHas('locations', function ($q) use ($locationId) {
                    $q->where('locations.id', $locationId);
                })->orWhereDoesntHave('locations');
            })
            ->exists();
    }

    /**
     * Retrieve holiday details if the date is a holiday.
     */
    public function getHolidayForUserLocation(User $user, $date): ?Holiday
    {
        $dateStr = Carbon::parse($date)->toDateString();
        $locationId = $user->employeeDetail ? $user->employeeDetail->location_id : null;

        $query = Holiday::with('holidayType')
            ->where('status', 'published')
            ->whereDate('holiday_date', $dateStr);

        if ($locationId) {
            $query->where(function ($q) use ($locationId) {
                $q->whereHas('locations', function ($l) use ($locationId) {
                    $l->where('locations.id', $locationId);
                })->orWhereDoesntHave('locations');
            });
        } else {
            $query->whereDoesntHave('locations');
        }

        return $query->first();
    }

    /**
     * Create reminder entries (1, 3, 7 days before).
     */
    protected function scheduleReminders(Holiday $holiday)
    {
        // Clear any existing reminders
        HolidayReminder::where('holiday_id', $holiday->id)->delete();

        $days = [1, 3, 7];
        $hDate = Carbon::parse($holiday->holiday_date);

        foreach ($days as $day) {
            $scheduledAt = $hDate->copy()->subDays($day)->startOfDay()->hour(9); // send reminders at 9:00 AM

            // Only schedule if the date is in the future
            if ($scheduledAt->isAfter(now())) {
                HolidayReminder::create([
                    'holiday_id' => $holiday->id,
                    'reminder_days_before' => $day,
                    'status' => 'pending',
                    'scheduled_at' => $scheduledAt,
                ]);
            }
        }
    }
}
