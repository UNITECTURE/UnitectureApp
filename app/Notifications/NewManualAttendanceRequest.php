<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Channels\TelegramChannel;
use App\Models\ManualAttendanceRequest;

class NewManualAttendanceRequest extends Notification implements ShouldQueue
{
    use Queueable;

    public $request;

    /**
     * Create a new notification instance.
     */
    public function __construct(ManualAttendanceRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return [TelegramChannel::class];
    }

    /**
     * Get the Telegram representation of the notification.
     */
    public function toTelegram(object $notifiable)
    {
        $employeeName = $this->request->user->name ?? 'Unknown Employee';
        $date = \Carbon\Carbon::parse($this->request->date)->format('M d, Y');
        $duration = $this->request->duration;
        $reason = $this->request->reason ?? 'No reason provided';

        return "<b>🚨 New Attendance Request</b>\n\n" .
               "👤 <b>Employee:</b> {$employeeName}\n" .
               "📅 <b>Date:</b> {$date}\n" .
               "⏱️ <b>Duration:</b> {$duration}\n" .
               "📝 <b>Reason:</b> {$reason}\n\n" .
               "<i>Please login to the portal to approve or reject this request.</i>";
    }
}
