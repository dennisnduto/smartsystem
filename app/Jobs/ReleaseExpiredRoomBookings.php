<?php

namespace App\Jobs;

use App\Models\RoomBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class ReleaseExpiredRoomBookings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $now = Carbon::now();
        
        $expiredBookings = RoomBooking::where('status', 'active')
            ->where('booking_date', '<=', $now->toDateString())
            ->where('end_time', '<=', $now->format('H:i:s'))
            ->get();

        foreach ($expiredBookings as $booking) {
            $booking->update([
                'status' => 'completed',
                'auto_released_at' => $now,
            ]);
        }
    }
}
