<div 
    x-data="{
        map: null,
        marker: null,
        circle: null,
        latitude: @entangle('data.latitude'),
        longitude: @entangle('data.longitude'),
        radius: @entangle('data.radius'),
        
        init() {
            // Wait for page to load
            setTimeout(() => {
                this.initMap();
            }, 500);
        },
        
        initMap() {
            // Initialize map centered on Riyadh
            this.map = L.map(this.$refs.mapContainer).setView(
                [this.latitude || 24.7136, this.longitude || 46.6753], 
                13
            );
            
            // Add OpenStreetMap tiles (free)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(this.map);
            
            // Add marker and circle
            this.updateMarker();
            
            // Map click event to update location
            this.map.on('click', (e) => {
                this.latitude = e.latlng.lat.toFixed(7);
                this.longitude = e.latlng.lng.toFixed(7);
                this.updateMarker();
            });
            
            // Watch for changes
            this.$watch('latitude', () => this.updateMarker());
            this.$watch('longitude', () => this.updateMarker());
            this.$watch('radius', () => this.updateMarker());
        },
        
        updateMarker() {
            if (!this.map) return;
            
            // Remove old marker and circle
            if (this.marker) this.map.removeLayer(this.marker);
            if (this.circle) this.map.removeLayer(this.circle);
            
            const lat = parseFloat(this.latitude) || 24.7136;
            const lng = parseFloat(this.longitude) || 46.6753;
            const rad = parseInt(this.radius) || 100;
            
            // Add new marker
            this.marker = L.marker([lat, lng], {
                draggable: true
            }).addTo(this.map);
            
            this.marker.on('dragend', (e) => {
                const pos = e.target.getLatLng();
                this.latitude = pos.lat.toFixed(7);
                this.longitude = pos.lng.toFixed(7);
            });
            
            // Add circle
            this.circle = L.circle([lat, lng], {
                radius: rad,
                color: '#3b82f6',
                fillColor: '#3b82f6',
                fillOpacity: 0.2
            }).addTo(this.map);
            
            // Fit map to circle
            this.map.fitBounds(this.circle.getBounds());
        }
    }"
    x-init="init()"
    wire:ignore
>
    <div class="space-y-4">
        <!-- Map Container -->
        <div 
            x-ref="mapContainer" 
            class="w-full h-96 rounded-lg border-2 border-gray-300 dark:border-gray-600"
            style="min-height: 400px;"
        ></div>
        
        <!-- Instructions -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm text-blue-800 dark:text-blue-200">
                    <p class="font-semibold mb-1">كيفية الاستخدام:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>انقر على الخريطة لتحديد الموقع</li>
                        <li>اسحب العلامة الحمراء لتغيير الموقع</li>
                        <li>الدائرة الزرقاء توضح نطاق السياج الجغرافي</li>
                        <li>يمكنك أيضاً إدخال الإحداثيات يدوياً في الحقول أعلاه</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Load Leaflet CSS and JS -->
    @once
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
              crossorigin=""/>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
                crossorigin=""></script>
    @endonce
</div>
