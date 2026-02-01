<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            @php
                $userId = auth()->id();
                $thisMonth = \App\Models\Attendance::where('user_id', $userId)
                    ->whereMonth('date', now()->month)
                    ->count();
                $onTime = \App\Models\Attendance::where('user_id', $userId)
                    ->whereMonth('date', now()->month)
                    ->where('is_late', false)
                    ->count();
                $totalHours = \App\Models\Attendance::where('user_id', $userId)
                    ->whereMonth('date', now()->month)
                    ->sum('work_minutes');
                $avgHours = $thisMonth > 0 ? round($totalHours / $thisMonth / 60, 1) : 0;
            @endphp

            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-primary-600">{{ $thisMonth }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">أيام الحضور هذا الشهر</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-success-600">{{ $onTime }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">أيام الالتزام بالموعد</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-info-600">{{ $avgHours }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">متوسط ساعات العمل</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-warning-600">{{ $thisMonth > 0 ? round(($onTime / $thisMonth) * 100) : 0 }}%</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">نسبة الالتزام</div>
                </div>
            </x-filament::section>
        </div>

        {{-- Attendance Table --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
