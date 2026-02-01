<?php

namespace App\Livewire\Dashboard;

use App\Models\Attendance;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class AttendanceWidget extends Widget
{
    public ?Attendance $todayAttendance = null;
    public bool $hasCheckedIn = false;
    public bool $hasCheckedOut = false;
    public ?string $message = null;
    public ?string $messageType = null;

    protected AttendanceService $attendanceService;

    public function boot(AttendanceService $attendanceService): void
    {
        $this->attendanceService = $attendanceService;
    }

    public function mount(): void
    {
        $this->loadTodayAttendance();
    }

    public function loadTodayAttendance(): void
    {
        $this->todayAttendance = $this->attendanceService->getTodayAttendance(auth()->user());
        
        if ($this->todayAttendance) {
            $this->hasCheckedIn = $this->todayAttendance->check_in_time !== null;
            $this->hasCheckedOut = $this->todayAttendance->check_out_time !== null;
        }
    }

    public function checkIn(float $lat, float $lon): void
    {
        $result = $this->attendanceService->checkIn(auth()->user(), $lat, $lon);

        $this->message = $result['message'];
        $this->messageType = $result['success'] ? 'success' : 'error';

        if ($result['success']) {
            $this->loadTodayAttendance();
            
            if (!$result['within_range']) {
                $this->message .= ' - تنبيه: أنت خارج نطاق الموقع المحدد';
                $this->messageType = 'warning';
            }
        }

        $this->dispatch('attendance-updated');
    }

    public function checkOut(float $lat, float $lon): void
    {
        $result = $this->attendanceService->checkOut(auth()->user(), $lat, $lon);

        $this->message = $result['message'];
        $this->messageType = $result['success'] ? 'success' : 'error';

        if ($result['success']) {
            $this->loadTodayAttendance();
            
            if (!$result['within_range']) {
                $this->message .= ' - تنبيه: أنت خارج نطاق الموقع المحدد';
                $this->messageType = 'warning';
            }
        }

        $this->dispatch('attendance-updated');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.dashboard.attendance-widget', [
            'currentTime' => Carbon::now()->format('H:i'),
            'currentDate' => Carbon::now()->locale('ar')->isoFormat('dddd، D MMMM YYYY'),
        ]);
    }
}
