<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoCheckoutJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     * Automatically checks out users who forgot to check out after their shift ended
     */
    public function handle(): void
    {
        Log::info('AutoCheckoutJob: Starting automatic checkout process');

        try {
            DB::beginTransaction();

            // Get all attendance records with check_in but no check_out from today or earlier
            $attendances = Attendance::whereNotNull('check_in_time')
                ->whereNull('check_out_time')
                ->where('date', '<=', now()->format('Y-m-d'))
                ->with(['user'])
                ->get();

            $checkedOutCount = 0;

            foreach ($attendances as $attendance) {
                if (!$attendance->user) {
                    continue;
                }

                // Get user's work shift through relationship
                $shift = $attendance->user->workShift()->first();
                
                if (!$shift) {
                    continue;
                }

                $shiftEndTime = Carbon::parse($attendance->date . ' ' . $shift->end_time);
                
                // Add 2 hours buffer after shift end time
                $bufferEndTime = $shiftEndTime->copy()->addHours(2);

                // If current time is past buffer time, perform auto checkout
                if (now()->greaterThan($bufferEndTime)) {
                    // Set checkout time to shift end time + 30 minutes
                    $autoCheckoutTime = $shiftEndTime->copy()->addMinutes(30);

                    $attendance->update([
                        'check_out_time' => $autoCheckoutTime->format('H:i:s'),
                        'status' => 'system_checkout',
                        'check_out_lat' => $attendance->check_in_lat,
                        'check_out_lng' => $attendance->check_in_lng,
                        'notes' => 'تسجيل خروج تلقائي من قبل النظام - لم يتم تسجيل الخروج يدوياً',
                    ]);

                    // Calculate total hours
                    $checkInTime = Carbon::parse($attendance->date . ' ' . $attendance->check_in_time);
                    $checkOutTime = Carbon::parse($attendance->date . ' ' . $autoCheckoutTime->format('H:i:s'));
                    $totalMinutes = $checkOutTime->diffInMinutes($checkInTime);
                    $totalHours = round($totalMinutes / 60, 2);

                    $attendance->update([
                        'total_hours' => $totalHours,
                    ]);

                    $checkedOutCount++;

                    // Send notification to user
                    try {
                        $notificationService = app(NotificationService::class);
                        $notificationService->sendDatabaseNotification(
                            $attendance->user,
                            'تسجيل خروج تلقائي',
                            "تم تسجيل خروجك تلقائياً في {$autoCheckoutTime->format('H:i')} لأنك نسيت تسجيل الخروج.",
                            'warning',
                            route('filament.employee.pages.my-attendance')
                        );
                    } catch (\Exception $e) {
                        Log::error('AutoCheckoutJob: Failed to send notification', [
                            'user_id' => $attendance->user_id,
                            'error' => $e->getMessage()
                        ]);
                    }

                    Log::info('AutoCheckoutJob: Checked out user', [
                        'user_id' => $attendance->user_id,
                        'attendance_id' => $attendance->id,
                        'checkout_time' => $autoCheckoutTime->toDateTimeString(),
                    ]);
                }
            }

            DB::commit();

            Log::info('AutoCheckoutJob: Completed successfully', [
                'total_records_checked' => $attendances->count(),
                'checked_out_count' => $checkedOutCount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AutoCheckoutJob: Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('AutoCheckoutJob: Job failed permanently', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
