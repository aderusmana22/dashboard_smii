<div id="jobDetailModal"
    class="hidden fixed inset-0 z-50 overflow-y-auto backdrop-blur-xl bg-gray-900/50 transition-opacity">

    <div class="flex items-center justify-center min-h-screen p-4">

        <div
            class="relative bg-white w-full max-w-5xl rounded-lg shadow-2xl flex flex-col h-[60vh] max-h-[60vh] overflow-hidden border border-gray-100">

            <div class="flex-shrink-0 flex justify-between items-center p-5 border-b border-gray-200 bg-white z-20">

                <h3 class="text-xl font-bold text-gray-800">Job Timeline & Full Details</h3>

                <button type="button" onclick="document.getElementById('jobDetailModal').classList.add('hidden')"
                    class="close-detail-btn text-gray-400 hover:text-red-500 transition-colors focus:outline-none p-1 rounded-md hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <div id="jobDetailContent" class="flex-1 overflow-y-auto p-0 bg-gray-50/50 custom-scrollbar">

                <div class="flex justify-center items-center h-full">
                    <div class="flex flex-col items-center">
                        <div
                            class="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-3">
                        </div>

                        <span class="text-gray-500 font-medium text-sm">Loading details...</span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>