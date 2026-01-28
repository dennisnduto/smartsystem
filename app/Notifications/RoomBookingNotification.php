<?php

namespace App\Notifications;

use App\Models\RoomBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RoomBookingNotification extends Notification
{
    use Queueable;

    public function __construct(public RoomBooking $booking)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => sprintf(
                'A special session has been scheduled: %s on %s from %s to %s in %s',
                $this->booking->unit->code ?? $this->booking->course->name ?? 'Special Session',
                $this->booking->booking_date->format('M d, Y'),
                $this->booking->start_time->format('H:i'),
                $this->booking->end_time->format('H:i'),
                $this->booking->room->name
            ),
            'booking_id' => $this->booking->id,
            'type' => 'room_booking',
        ];
    }
}
