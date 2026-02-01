<x-filament-widgets::widget>
    <x-filament::section>
        {{-- Check if user has attendance.create permission --}}
        @if(auth()->user()->can('attendance.create') || auth()->user()->is_super_admin)
        <div x-data="{
            loading: false,
            getLocationAndCheckIn() {
                this.loading = true;
                if (!navigator.geolocation) {
                    alert('المتصفح لا يدعم تحديد الموقع الجغرافي');
                    this.loading = false;
                    return;
                }
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        $wire.checkIn(position.coords.latitude, position.coords.longitude);
                        this.loading = false;
                    },
                    (error) => {
                        alert('فشل تحديد الموقع. يرجى السماح بالوصول إلى الموقع.');
                        console.error('Geolocation error:', error);
                        this.loading = false;
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            },
            getLocationAndCheckOut() {
                this.loading = true;
                if (!navigator.geolocation) {
                    alert('المتصفح لا يدعم تحديد الموقع الجغرافي');
                    this.loading = false;
                    return;
                }
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        $wire.checkOut(position.coords.latitude, position.coords.longitude);
                        this.loading = false;
                    },
                    (error) => {
                        alert('فشل تحديد الموقع. يرجى السماح بالوصول إلى الموقع.');
                        console.error('Geolocation error:', error);
                        this.loading = false;
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            }
        }" class="min-h-[300px] sm:min-h-[350px]">
            {{-- Header - Responsive --}}
            <div class="text-center mb-4 sm:mb-6">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-200 mb-2">تسجيل الحضور والانصراف</h2>
                <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">{{ $currentDate }}</p>
                <p class="text-2xl sm:text-3xl font-bold text-primary-600 mt-2">{{ $currentTime }}</p>
            </div>

            @if($message)
                <x-filament::badge 
                    :color="$messageType === 'success' ? 'success' : ($messageType === 'warning' ? 'warning' : 'danger')"
                    class="w-full mb-4 text-center py-3"
                >
                    {{ $message }}
                </x-filament::badge>
            @endif

            @if($todayAttendance)
                <div class="mb-4 sm:mb-6 space-y-2 sm:space-y-3">
                    @if($hasCheckedIn)
                        <div class="flex items-center justify-between bg-success-50 dark:bg-success-900/20 p-3 sm:p-4 rounded-lg">
                            <span class="text-sm sm:text-base text-gray-700 dark:text-gray-300">وقت الحضور:</span>
                            <span class="font-bold text-base sm:text-lg text-success-700 dark:text-success-400">{{ \Carbon\Carbon::parse($todayAttendance->check_in_time)->format('H:i') }}</span>
                        </div>
                    @endif

                    @if($hasCheckedOut)
                        <div class="flex items-center justify-between bg-primary-50 dark:bg-primary-900/20 p-3 sm:p-4 rounded-lg">
                            <span class="text-sm sm:text-base text-gray-700 dark:text-gray-300">وقت الانصراف:</span>
                            <span class="font-bold text-base sm:text-lg text-primary-700 dark:text-primary-400">{{ \Carbon\Carbon::parse($todayAttendance->check_out_time)->format('H:i') }}</span>
                        </div>

                        @if($todayAttendance->work_minutes)
                            <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-800 p-3 sm:p-4 rounded-lg">
                                <span class="text-sm sm:text-base text-gray-700 dark:text-gray-300">ساعات العمل:</span>
                                <span class="font-bold text-sm sm:text-base text-gray-700 dark:text-gray-300">{{ floor($todayAttendance->work_minutes / 60) }} ساعة {{ $todayAttendance->work_minutes % 60 }} دقيقة</span>
                            </div>
                        @endif
                    @endif
                </div>
            @endif

            <div class="space-y-3">
                @if(!$hasCheckedIn)
                    <x-filament::button 
                        @click="getLocationAndCheckIn()"
                        color="success"
                        size="lg"
                        class="w-full min-h-[44px] text-base sm:text-lg"
                        icon="heroicon-o-check-circle"
                        x-bind:disabled="loading"
                    >
                        <span x-show="!loading">تسجيل الحضور</span>
                        <span x-show="loading" class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            جاري تحديد الموقع...
                        </span>
                    </x-filament::button>
                @elseif(!$hasCheckedOut)
                    <x-filament::button 
                        @click="getLocationAndCheckOut()"
                        color="primary"
                        size="lg"
                        class="w-full min-h-[44px] text-base sm:text-lg"
                        icon="heroicon-o-arrow-right-on-rectangle"
                        x-bind:disabled="loading"
                    >
                        <span x-show="!loading">تسجيل الانصراف</span>
                        <span x-show="loading" class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            جاري تحديد الموقع...
                        </span>
                    </x-filament::button>
                @else
                    <div class="text-center p-4 bg-gray-100 dark:bg-gray-800 rounded-lg">
                        <p class="text-sm sm:text-base text-gray-700 dark:text-gray-300 font-semibold">تم تسجيل الحضور والانصراف لهذا اليوم</p>
                    </div>
                @endif
            </div>
        </div>
        @else
        {{-- No Permission Message - Mobile Friendly --}}
        <div class="text-center p-6 sm:p-8 bg-gray-50 dark:bg-gray-800 rounded-lg min-h-[300px] flex items-center justify-center">
            <div>
                <svg class="mx-auto h-12 w-12 sm:h-16 sm:w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <h3 class="text-base sm:text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">غير مصرح لك</h3>
                <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">ليس لديك صلاحية تسجيل الحضور</p>
            </div>
        </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>

@push('scripts')
<script>
// Live Location Tracking System
(function() {
    'use strict';
    
    const TRACKING_INTERVAL = 5 * 60 * 1000; // 5 minutes
    const SIGNIFICANT_MOVEMENT = 50; // meters
    const OFFLINE_STORAGE_KEY = 'sarh_offline_locations';
    
    let lastPosition = null;
    let watchId = null;
    let trackingInterval = null;
    
    // Check if user is authenticated and tracking is enabled
    const isTrackingEnabled = {{ auth()->check() && auth()->user()->is_active ? 'true' : 'false' }};
    
    if (!isTrackingEnabled) {
        console.log('Location tracking disabled');
        return;
    }
    
    /**
     * Send location to server
     */
    async function sendLocation(position, type = 'ping') {
        const data = {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy,
            speed: position.coords.speed || null,
            battery_level: await getBatteryLevel(),
            type: type,
            tracked_at: new Date().toISOString(),
            is_mock_location: false
        };
        
        try {
            const response = await fetch('/api/tracking/log', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify(data)
            });
            
            if (response.ok) {
                console.log('Location sent successfully');
                return true;
            } else {
                console.error('Failed to send location:', response.statusText);
                storeOffline(data);
                return false;
            }
        } catch (error) {
            console.error('Error sending location:', error);
            storeOffline(data);
            return false;
        }
    }
    
    /**
     * Store location offline for later sync
     */
    function storeOffline(data) {
        try {
            const stored = JSON.parse(localStorage.getItem(OFFLINE_STORAGE_KEY) || '[]');
            stored.push(data);
            
            // Keep only last 100 entries
            if (stored.length > 100) {
                stored.shift();
            }
            
            localStorage.setItem(OFFLINE_STORAGE_KEY, JSON.stringify(stored));
            console.log('Location stored offline');
        } catch (error) {
            console.error('Error storing location offline:', error);
        }
    }
    
    /**
     * Sync offline locations when back online
     */
    async function syncOfflineLocations() {
        try {
            const stored = JSON.parse(localStorage.getItem(OFFLINE_STORAGE_KEY) || '[]');
            
            if (stored.length === 0) return;
            
            const response = await fetch('/api/tracking/batch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify({ locations: stored })
            });
            
            if (response.ok) {
                localStorage.removeItem(OFFLINE_STORAGE_KEY);
                console.log('Offline locations synced successfully');
            }
        } catch (error) {
            console.error('Error syncing offline locations:', error);
        }
    }
    
    /**
     * Calculate distance between two coordinates in meters
     */
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000; // Earth's radius in meters
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }
    
    /**
     * Get battery level if available
     */
    async function getBatteryLevel() {
        if ('getBattery' in navigator) {
            try {
                const battery = await navigator.getBattery();
                return Math.round(battery.level * 100);
            } catch (error) {
                return null;
            }
        }
        return null;
    }
    
    /**
     * Handle position update
     */
    function handlePosition(position) {
        // Check if significant movement occurred
        if (lastPosition) {
            const distance = calculateDistance(
                lastPosition.coords.latitude,
                lastPosition.coords.longitude,
                position.coords.latitude,
                position.coords.longitude
            );
            
            if (distance > SIGNIFICANT_MOVEMENT) {
                sendLocation(position, 'route');
                lastPosition = position;
            }
        } else {
            sendLocation(position, 'ping');
            lastPosition = position;
        }
    }
    
    /**
     * Start tracking
     */
    function startTracking() {
        if (!navigator.geolocation) {
            console.error('Geolocation not supported');
            return;
        }
        
        // Watch position for significant changes
        watchId = navigator.geolocation.watchPosition(
            handlePosition,
            (error) => console.error('Geolocation error:', error),
            {
                enableHighAccuracy: false,
                maximumAge: 60000,
                timeout: 27000
            }
        );
        
        // Periodic tracking (every 5 minutes)
        trackingInterval = setInterval(() => {
            navigator.geolocation.getCurrentPosition(
                (position) => sendLocation(position, 'ping'),
                (error) => console.error('Periodic tracking error:', error),
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }, TRACKING_INTERVAL);
        
        console.log('Location tracking started');
    }
    
    /**
     * Stop tracking
     */
    function stopTracking() {
        if (watchId) {
            navigator.geolocation.clearWatch(watchId);
            watchId = null;
        }
        
        if (trackingInterval) {
            clearInterval(trackingInterval);
            trackingInterval = null;
        }
        
        console.log('Location tracking stopped');
    }
    
    // Initialize tracking on page load
    document.addEventListener('DOMContentLoaded', () => {
        startTracking();
        
        // Sync offline locations when coming online
        window.addEventListener('online', syncOfflineLocations);
        
        // Stop tracking when page is hidden
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                stopTracking();
            } else {
                startTracking();
            }
        });
    });
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', stopTracking);
})();
</script>
@endpush
