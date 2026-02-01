<x-filament-panels::page>
    <div class="space-y-6">
        <!-- User Points Display -->
        <x-filament::section>
            <div class="text-center">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">نقاطك الحالية</h3>
                <div class="text-4xl font-bold text-primary-600 dark:text-primary-400">⭐ {{ number_format($userPoints) }}</div>
            </div>
        </x-filament::section>

        <!-- Rewards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($rewards as $reward)
                <x-filament::section>
                    <div class="space-y-4">
                        <div class="text-center">
                            @if($reward['icon'])
                                <div class="text-6xl mb-3">{{ $reward['icon'] }}</div>
                            @else
                                <div class="w-20 h-20 mx-auto bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center mb-3">
                                    <span class="text-4xl">🎁</span>
                                </div>
                            @endif
                        </div>

                        <div class="text-center">
                            <h4 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">{{ $reward['name'] }}</h4>
                            
                            @if($reward['description'])
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ $reward['description'] }}</p>
                            @endif

                            <div class="inline-flex items-center gap-2 bg-primary-50 dark:bg-primary-900/20 px-4 py-2 rounded-full mb-3">
                                <span class="text-2xl">⭐</span>
                                <span class="font-bold text-primary-700 dark:text-primary-300">{{ number_format($reward['points_required']) }}</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">نقطة</span>
                            </div>

                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-3">المتبقي: {{ $reward['stock'] }}</div>

                            <x-filament::button
                                wire:click="redeemReward({{ $reward['id'] }})"
                                color="primary"
                                size="lg"
                                class="w-full"
                                :disabled="$userPoints < $reward['points_required'] || $reward['stock'] <= 0"
                            >
                                @if($userPoints < $reward['points_required'])
                                    🔒 نقاط غير كافية
                                @elseif($reward['stock'] <= 0)
                                    ❌ نفذت الكمية
                                @else
                                    🛒 استبدال الآن
                                @endif
                            </x-filament::button>
                        </div>
                    </div>
                </x-filament::section>
            @empty
                <div class="col-span-full">
                    <x-filament::section>
                        <div class="text-center py-12">
                            <div class="text-6xl mb-4">🎁</div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">لا توجد مكافآت متاحة حالياً</h3>
                            <p class="text-gray-600 dark:text-gray-400">تابع كسب النقاط وتحقق لاحقاً!</p>
                        </div>
                    </x-filament::section>
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
