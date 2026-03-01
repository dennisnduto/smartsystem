<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RoomBooking;
use Illuminate\Support\Facades\Log;

class ReleaseExpiredBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:release-expired-bookings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release expired room bookings automatically';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Releasing expired room bookings...');

        // Find bookings that have expired
        $expiredBookings = RoomBooking::where('status', 'active')
            ->where(function($query) {
                $query->where('auto_released_at', '<=', now())
                      ->orWhere(function($q) {
                          $q->where('booking_date', '<', now()->toDateString())
                            ->orWhere(function($subQ) {
                                $subQ->where('booking_date', now()->toDateString())
                                      ->where('end_time', '<', now()->format('H:i:s'));
                            });
                      });
            })
            ->get();

        $releasedCount = 0;
        
        foreach ($expiredBookings as $booking) {
            $booking->update(['status' => 'expired']);
            $releasedCount++;
            
            $this->line("Released booking: {$booking->room->name} - {$booking->purpose} ({$booking->booking_date} {$booking->start_time})");
            
            Log::info('Room booking expired and released', [
                'booking_id' => $booking->id,
                'room_id' => $booking->room_id,
                'lecturer_id' => $booking->lecturer_id,
                'expired_at' => now(),
            ]);
        }

        $this->info("Successfully released {$releasedCount} expired bookings.");
        
        return $releasedCount;
    }
}
