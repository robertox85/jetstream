<!-- resources/views/filament/components/google-calendar-banner.blade.php -->
<div id="google-calendar-banner" class="relative isolate flex items-center gap-x-6 overflow-hidden bg-red-50 px-6 py-2.5 sm:px-3.5 sm:before:flex-1">
    <div
            class="absolute left-[max(-7rem,calc(50%-52rem))] top-1/2 -z-10 -translate-y-1/2 transform-gpu blur-2xl"
            aria-hidden="true"
    >
        <div
                class="aspect-[577/310] w-[36.0625rem] bg-gradient-to-r from-red-500/20 to-red-900/20 opacity-30"
                style="clip-path: polygon(74.8% 41.9%, 97.2% 73.2%, 100% 34.9%, 92.5% 0.4%, 87.5% 0%, 75% 28.6%, 58.5% 54.6%, 50.1% 56.8%, 46.9% 44%, 48.3% 17.4%, 24.7% 53.9%, 0% 27.9%, 11.9% 74.2%, 24.9% 54.1%, 68.6% 100%, 74.8% 41.9%)"
        ></div>
    </div>
    <div
            class="absolute left-[max(45rem,calc(50%+8rem))] top-1/2 -z-10 -translate-y-1/2 transform-gpu blur-2xl"
            aria-hidden="true"
    >
        <div
                class="aspect-[577/310] w-[36.0625rem] bg-gradient-to-r from-red-500/20 to-red-900/20 opacity-30"
                style="clip-path: polygon(74.8% 41.9%, 97.2% 73.2%, 100% 34.9%, 92.5% 0.4%, 87.5% 0%, 75% 28.6%, 58.5% 54.6%, 50.1% 56.8%, 46.9% 44%, 48.3% 17.4%, 24.7% 53.9%, 0% 27.9%, 11.9% 74.2%, 24.9% 54.1%, 68.6% 100%, 74.8% 41.9%)"
        ></div>
    </div>
    <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
        <div class="flex items-center gap-x-3">
            <svg class="h-5 w-5 flex-none text-red-600" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
            <p class="text-sm leading-6 text-gray-900">
                <strong class="font-semibold">Attenzione:</strong>
                <span class="inline-block">Google Calendar non connesso.</span>
            </p>
        </div>
        <div class="flex flex-1 justify-end">
            <a
                    href="{{ $connectRoute }}"
                    class="flex items-center gap-x-1.5 rounded-md bg-red-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600 transition duration-150 ease-in-out"
            >
                <svg class="-ml-0.5 h-5 w-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 7H7C5.89543 7 5 7.89543 5 9V18C5 19.1046 5.89543 20 7 20H17C18.1046 20 19 19.1046 19 18V9C19 7.89543 18.1046 7 17 7H15M9 7V5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5V7M9 7H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Riconnetti ora
            </a>
        </div>
    </div>
    <div class="flex flex-1 justify-end">
        <button type="button" onclick="this.closest('div').parentElement.remove()" class="flex items-center gap-x-1.5 text-gray-900 hover:text-gray-700 -m-3 p-3">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
            </svg>
        </button>
    </div>
</div>