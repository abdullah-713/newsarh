<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Attendance Widget --}}
        <div>
            @livewire(\App\Livewire\Dashboard\AttendanceWidget::class)
        </div>

        {{-- Quick Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @php
                $thisMonth = \App\Models\Attendance::where('user_id', auth()->id())
                    ->whereMonth('date', now()->month)
                    ->count();
                $points = auth()->user()->points ?? 0;
                $badges = \App\Models\UserBadge::where('user_id', auth()->id())->count();
            @endphp

            <x-filament::section>
                <div class="text-center">
                    <div class="text-4xl font-bold text-blue-600">{{ $thisMonth }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">أيام الحضور</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-4xl font-bold text-yellow-600">{{ $points }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">نقاطي</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-4xl font-bold text-green-600">{{ $badges }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">الشارات</div>
                </div>
            </x-filament::section>
        </div>

        {{-- Recent Activity --}}
        <x-filament::section>
            <x-slot name="heading">
                النشاط الأخير
            </x-slot>

            @php
                $recentAttendance = \App\Models\Attendance::where('user_id', auth()->id())
                    ->orderBy('date', 'desc')
                    ->limit(5)
                    ->get();
            @endphp

            @if($recentAttendance->count() > 0)
                <div class="space-y-2">
                    @foreach($recentAttendance as $record)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="text-2xl">
                                    @if($record->is_late)
                                        ⏰
                                    @else
                                        ✅
                                    @endif
                                </div>
                                <div>
                                    <div class="font-medium">{{ $record->date->format('Y-m-d') }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        الحضور: {{ $record->check_in_time->format('H:i') }}
                                        @if($record->check_out_time)
                                            | الانصراف: {{ $record->check_out_time->format('H:i') }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @if($record->is_late)
                                <span class="text-red-600 font-medium text-sm">
                                    متأخر {{ $record->late_minutes }} دقيقة
                                </span>
                            @else
                                <span class="text-green-600 font-medium text-sm">في الموعد ✓</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    لا يوجد سجلات حضور حتى الآن
                </div>
            @endif
        </x-filament::section>

        {{-- TRAP: Fake Salary Leak Button --}}
        @php
            $salaryTrap = \App\Models\TrapConfiguration::where('trap_type', 'fake_button')
                ->where('is_active', true)
                ->first();
        @endphp
        
        @if($salaryTrap)
            <div class="border-2 border-dashed border-yellow-300 dark:border-yellow-700 rounded-lg p-6 bg-yellow-50 dark:bg-yellow-950">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-yellow-900 dark:text-yellow-100">
                            🎁 عرض خاص للموظفين
                        </h3>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                            احصل على معلومات حصرية عن الرواتب والمكافآت
                        </p>
                    </div>
                </div>
                
                @livewire('traps.fake-salary-leak-button', [
                    'trapConfigId' => $salaryTrap->id,
                    'label' => $salaryTrap->trap_name_ar ?? 'تسريب الرواتب',
                    'icon' => '💰'
                ])
            </div>
        @endif

        {{-- TRAP: Prohibited Section --}}
        @php
            $prohibitedTrap = \App\Models\TrapConfiguration::where('trap_type', 'prohibited_section')
                ->where('is_active', true)
                ->first();
        @endphp
        
        @if($prohibitedTrap)
            <div class="mt-6">
                @livewire('traps.prohibited-section-trap', [
                    'trapConfigId' => $prohibitedTrap->id,
                    'title' => $prohibitedTrap->trap_name_ar ?? 'بيانات سرية',
                    'icon' => '🔒',
                    'description' => $prohibitedTrap->description ?? 'قسم مخصص للإدارة العليا فقط'
                ])
            </div>
        @endif

        {{-- Tips Section --}}
        <x-filament::section>
            <x-slot name="heading">
                💡 نصائح مفيدة
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-start gap-3 p-4 bg-blue-50 dark:bg-blue-900 rounded-lg">
                    <div class="text-2xl">📱</div>
                    <div>
                        <h4 class="font-semibold mb-1">فعّل الإشعارات</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            احصل على تذكير بمواعيد العمل والإنجازات
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-4 bg-green-50 dark:bg-green-900 rounded-lg">
                    <div class="text-2xl">🎯</div>
                    <div>
                        <h4 class="font-semibold mb-1">حقق الأهداف</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            اكسب شارات جديدة بالحضور المنتظم
                        </p>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
