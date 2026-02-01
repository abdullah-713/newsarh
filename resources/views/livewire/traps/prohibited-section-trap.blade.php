<div 
    x-data="{ showError: false }"
    x-on:show-fake-permission-error.window="showError = true; setTimeout(() => showError = false, 3000)"
>
    <div 
        wire:click="attemptAccess"
        class="relative cursor-pointer group bg-gradient-to-br from-red-50 to-pink-50 dark:from-red-950 dark:to-pink-950 border-2 border-red-200 dark:border-red-800 rounded-lg p-6 hover:shadow-lg transition-all duration-200"
    >
        <div class="flex items-center gap-4">
            <div class="text-4xl">{{ $sectionIcon }}</div>
            
            <div class="flex-1">
                <h3 class="text-lg font-bold text-red-900 dark:text-red-100 mb-1">
                    {{ $sectionTitle }}
                </h3>
                <p class="text-sm text-red-700 dark:text-red-300">
                    {{ $description }}
                </p>
            </div>

            <div class="text-red-400 group-hover:text-red-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </div>

        <!-- Badge "جديد" لجذب الانتباه -->
        <div class="absolute top-2 right-2">
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-500 text-white animate-pulse">
                جديد
            </span>
        </div>
    </div>

    <!-- رسالة خطأ وهمية -->
    <div 
        x-show="showError"
        x-transition
        class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
        role="alert"
    >
        <strong class="font-bold">⚠️ خطأ في الصلاحيات</strong>
        <span class="block sm:inline"> ليس لديك صلاحية الوصول لهذا القسم. يرجى التواصل مع الإدارة.</span>
    </div>
</div>
