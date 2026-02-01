<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Points Summary --}}
        <x-filament::section>
            <div class="text-center py-8">
                <div class="text-6xl font-bold text-primary-600 mb-2">{{ $this->getTotalPoints() }}</div>
                <div class="text-xl text-gray-600 dark:text-gray-400">إجمالي النقاط</div>
            </div>
        </x-filament::section>

        {{-- Earned Badges --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <span>شاراتي المكتسبة ({{ $this->getUserBadges()->count() }})</span>
                </div>
            </x-slot>

            @if($this->getUserBadges()->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($this->getUserBadges() as $userBadge)
                        <div class="border rounded-lg p-6 text-center bg-gradient-to-br from-yellow-50 to-orange-50 dark:from-yellow-900 dark:to-orange-900">
                            <div class="text-5xl mb-3">{{ $userBadge->badge->icon }}</div>
                            <h3 class="font-bold text-lg mb-2">{{ $userBadge->badge->name }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ $userBadge->badge->description }}</p>
                            <div class="flex items-center justify-center gap-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-800 dark:text-primary-100">
                                    +{{ $userBadge->badge->points }} نقطة
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 mt-3">
                                حصلت عليها: {{ $userBadge->awarded_at->diffForHumans() }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 text-gray-500">
                    <div class="text-6xl mb-4">🎯</div>
                    <p>لم تحصل على أي شارات بعد</p>
                    <p class="text-sm mt-2">واصل العمل بجد لكسب شاراتك الأولى!</p>
                </div>
            @endif
        </x-filament::section>

        {{-- Available Badges --}}
        <x-filament::section>
            <x-slot name="heading">
                الشارات المتاحة للكسب
            </x-slot>

            @if($this->getAvailableBadges()->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($this->getAvailableBadges() as $badge)
                        <div class="border rounded-lg p-6 text-center bg-gray-50 dark:bg-gray-800 opacity-75">
                            <div class="text-4xl mb-3 grayscale">{{ $badge->icon }}</div>
                            <h3 class="font-bold text-lg mb-2">{{ $badge->name }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ $badge->description }}</p>
                            <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                +{{ $badge->points }} نقطة
                            </div>
                            @if($badge->criteria)
                                <div class="mt-3 text-xs text-gray-500">
                                    {{ $badge->criteria }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <p>🎉 رائع! لقد حصلت على جميع الشارات المتاحة!</p>
                </div>
            @endif
        </x-filament::section>

        {{-- Tips --}}
        <x-filament::section>
            <x-slot name="heading">
                💡 نصائح لكسب المزيد من النقاط
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-start gap-3 p-4 bg-blue-50 dark:bg-blue-900 rounded-lg">
                    <div class="text-2xl">⏰</div>
                    <div>
                        <h4 class="font-semibold mb-1">احضر في الوقت المحدد</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">احصل على 5 نقاط إضافية عند الحضور بدون تأخير</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-4 bg-green-50 dark:bg-green-900 rounded-lg">
                    <div class="text-2xl">📍</div>
                    <div>
                        <h4 class="font-semibold mb-1">فعّل تحديد الموقع</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">احصل على 10 نقاط إضافية عند التحقق من الموقع</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-4 bg-yellow-50 dark:bg-yellow-900 rounded-lg">
                    <div class="text-2xl">🔥</div>
                    <div>
                        <h4 class="font-semibold mb-1">حافظ على التسلسل</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">احصل على شارة خاصة عند الحضور 7 أيام متتالية بدون تأخير</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 p-4 bg-purple-50 dark:bg-purple-900 rounded-lg">
                    <div class="text-2xl">🐦</div>
                    <div>
                        <h4 class="font-semibold mb-1">كن مبكراً</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">احضر قبل 30 دقيقة من موعدك واحصل على شارة "الطائر المبكر"</p>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
