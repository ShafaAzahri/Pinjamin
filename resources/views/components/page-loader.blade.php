<!-- Global Page Loader -->
<div 
    x-data="{ loading: false }"
    x-init="
        // Listen to beforeunload to show the loader when navigating away
        window.addEventListener('beforeunload', () => {
            loading = true;
        });

        // Listen to pageshow to hide the loader when returning via back button (BFCache)
        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                loading = false;
            }
        });

        // Also intercept all form submits to show loader (prevents double submits visually)
        document.addEventListener('submit', (e) => {
            // Optional: don't show loader if form has target='_blank'
            if(e.target.getAttribute('target') !== '_blank') {
                loading = true;
            }
        });
    "
    x-show="loading"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    style="display: none;"
    class="fixed inset-0 z-[9999] bg-slate-900/40 backdrop-blur-sm flex items-center justify-center"
>
    <div class="bg-white p-6 rounded-2xl shadow-2xl flex flex-col items-center border border-slate-100">
        <!-- Spinner -->
        <svg class="animate-spin h-10 w-10 text-teal-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-sm font-bold text-slate-700 tracking-wide">Memuat...</span>
    </div>
</div>
