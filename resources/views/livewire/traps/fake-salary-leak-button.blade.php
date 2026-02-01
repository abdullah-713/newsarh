<div>
    <button 
        wire:click="triggerTrap"
        type="button"
        class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105"
        x-data="{ loading: false }"
        x-on:show-fake-loader.window="loading = true; setTimeout(() => { $el.style.display = 'none' }, 2000)"
        x-bind:disabled="loading"
    >
        <span x-show="!loading" class="text-xl">{{ $buttonIcon }}</span>
        
        <svg x-show="loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        
        <span x-show="!loading">{{ $buttonLabel }}</span>
        <span x-show="loading">جاري التحميل...</span>
    </button>
</div>
