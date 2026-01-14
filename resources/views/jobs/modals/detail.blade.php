<div id="jobDetailModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-80 transition-opacity">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white dark:bg-gray-800 w-full max-w-4xl rounded-lg shadow-xl flex flex-col max-h-[90vh]">
            <div class="flex justify-between items-center p-4 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-700 rounded-t-lg">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Job Timeline & Full Details</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-2xl font-bold close-detail-btn">&times;</button>
            </div>
            <div id="jobDetailContent" class="p-6 overflow-y-auto flex-1">
                <!-- AJAX Content Loaded Here -->
                <div class="flex justify-center items-center h-40">
                     <div class="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                </div>
            </div>
        </div>
    </div>
</div>