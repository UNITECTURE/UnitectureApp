<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Channels\TelegramChannel;
use App\Models\ManualAttendanceRequest;

class ManualAttendanceStatusUpdated extends Notification implements ShouldQueue
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
        $date = \Carbon\Carbon::parse($this->request->date)->format('M d, Y');
        $statusRaw = $this->request->status; // approved / rejected
        $status = ucfirst($statusRaw);
        
        // Emoji based on status
        $emoji = $statusRaw === 'approved' ? '✅' : '❌';
        $color = $statusRaw === 'approved' ? 'approved' : 'rejected';

        return "{$emoji} <b>Attendance {$status}</b>\n\n" .
               "Your manual attendance request for <b>{$date}</b> has been <b>{$color}</b>.\n\n" .
               "⏱️ <b>Duration:</b> {$this->request->duration}\n" .
               ($this->request->rejection_reason ? "📝 <b>Reason:</b> {$this->request->rejection_reason}" : "");
    }
}
