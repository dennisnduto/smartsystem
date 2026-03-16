<?php

namespace App\Notifications;

use App\Models\Timetable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TimetablePublished extends Notification
{
    use Queueable;

    protected $timetable;

    /**
     * Create a new notification instance.
     */
    public function __construct(Timetable $timetable)
    {
        $this->timetable = $timetable;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Official Timetable Published')
                    ->line('A new official timetable for ' . $this->timetable->institution->name . ' has been published.')
                    ->line('Timetable Name: ' . $this->timetable->name)
                    ->line('Academic Year: ' . ($this->timetable->academic_year ?? 'All'))
                    ->line('Semester: ' . $this->timetable->semester)
                    ->action('View Timetable', url('/dashboard'))
                    ->line('Thank you for using our Smart System!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'timetable_id' => $this->timetable->id,
            'title' => 'Official Timetable Published',
            'message' => 'The ' . $this->timetable->name . ' timetable is now available for ' . $this->timetable->semester . '.',
            'institution' => $this->timetable->institution->name ?? 'System',
            'type' => 'timetable_published'
        ];
    }
}
