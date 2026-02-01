<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-3">
            <div class="flex items-center gap-2 mb-4">
                <span class="text-2xl">📢</span>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    الإعلانات
                </h3>
            </div>

            @php
                $announcements = $this->getAnnouncements();
            @endphp

            @forelse($announcements as $announcement)
                <div class="p-4 rounded-lg border 
                    @if($announcement->type === 'success') border-success-200 bg-success-50 dark:bg-success-900/20
                    @elseif($announcement->type === 'warning') border-warning-200 bg-warning-50 dark:bg-warning-900/20
                    @elseif($announcement->type === 'danger') border-danger-200 bg-danger-50 dark:bg-danger-900/20
                    @else border-primary-200 bg-primary-50 dark:bg-primary-900/20
                    @endif
                ">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl">{{ $announcement->icon }}</span>
                        <div class="flex-1">
                            <h4 class="font-bold text-gray-900 dark:text-gray-100 mb-1">
                                {{ $announcement->title }}
                            </h4>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                {{ $announcement->body }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                {{ $announcement->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <span class="text-4xl mb-2 block">📭</span>
                    <p class="text-gray-600 dark:text-gray-400">
                        لا توجد إعلانات جديدة
                    </p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
