<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            <div class="text-center">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    😊 كيف تشعر اليوم؟
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    شاركنا شعورك لنساعدك على تحسين تجربتك
                </p>
            </div>

            <div class="flex justify-center gap-4">
                <button 
                    wire:click="saveMood('very_happy')"
                    class="flex flex-col items-center p-3 rounded-lg hover:bg-success-50 dark:hover:bg-success-900/20 transition
                           {{ $todayMood?->mood === 'very_happy' ? 'bg-success-100 dark:bg-success-900/30 ring-2 ring-success-500' : '' }}"
                >
                    <span class="text-4xl mb-1">😄</span>
                    <span class="text-xs text-gray-600 dark:text-gray-400">رائع</span>
                </button>

                <button 
                    wire:click="saveMood('happy')"
                    class="flex flex-col items-center p-3 rounded-lg hover:bg-success-50 dark:hover:bg-success-900/20 transition
                           {{ $todayMood?->mood === 'happy' ? 'bg-success-100 dark:bg-success-900/30 ring-2 ring-success-500' : '' }}"
                >
                    <span class="text-4xl mb-1">😊</span>
                    <span class="text-xs text-gray-600 dark:text-gray-400">جيد</span>
                </button>

                <button 
                    wire:click="saveMood('neutral')"
                    class="flex flex-col items-center p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition
                           {{ $todayMood?->mood === 'neutral' ? 'bg-gray-100 dark:bg-gray-800 ring-2 ring-gray-500' : '' }}"
                >
                    <span class="text-4xl mb-1">😐</span>
                    <span class="text-xs text-gray-600 dark:text-gray-400">عادي</span>
                </button>

                <button 
                    wire:click="saveMood('sad')"
                    class="flex flex-col items-center p-3 rounded-lg hover:bg-warning-50 dark:hover:bg-warning-900/20 transition
                           {{ $todayMood?->mood === 'sad' ? 'bg-warning-100 dark:bg-warning-900/30 ring-2 ring-warning-500' : '' }}"
                >
                    <span class="text-4xl mb-1">😟</span>
                    <span class="text-xs text-gray-600 dark:text-gray-400">حزين</span>
                </button>

                <button 
                    wire:click="saveMood('very_sad')"
                    class="flex flex-col items-center p-3 rounded-lg hover:bg-danger-50 dark:hover:bg-danger-900/20 transition
                           {{ $todayMood?->mood === 'very_sad' ? 'bg-danger-100 dark:bg-danger-900/30 ring-2 ring-danger-500' : '' }}"
                >
                    <span class="text-4xl mb-1">😢</span>
                    <span class="text-xs text-gray-600 dark:text-gray-400">سيء</span>
                </button>
            </div>

            @if($todayMood)
                <div class="text-center text-sm text-gray-600 dark:text-gray-400 mt-3">
                    ✓ شكراً على مشاركتنا شعورك اليوم
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
