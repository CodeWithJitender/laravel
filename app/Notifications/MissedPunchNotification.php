<?php

namespace App\Notifications;

use App\Models\Attendance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MissedPunchNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $attendance;

    public function __construct(Attendance $attendance)
    {
        $this->attendance = $attendance;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $dateStr = $this->attendance->attendance_date->format('M d, Y');
        return (new MailMessage)
            ->subject('Missed Clock Out Alert - ' . $dateStr)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Our records indicate that you clocked in on ' . $dateStr . ' at ' . $this->attendance->clock_in->format('h:i A') . ', but did not record a clock out.')
            ->line('As a result, your attendance status has been updated to "Missed Punch".')
            ->action('Submit Correction Request', route('attendance.corrections.create', ['date' => $this->attendance->attendance_date->toDateString()]))
            ->line('Please submit a correction request to adjust your punch times so that your working hours can be calculated accurately.')
            ->line('Thank you!');
    }

    public function toArray($notifiable): array
    {
        return [
            'attendance_id' => $this->attendance->id,
            'attendance_date' => $this->attendance->attendance_date->toDateString(),
            'message' => 'You missed clock-out on ' . $this->attendance->attendance_date->format('M d, Y') . '. Please submit a correction request.',
            'action_url' => route('attendance.corrections.create', ['date' => $this->attendance->attendance_date->toDateString()]),
        ];
    }
}
