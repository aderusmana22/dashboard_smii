<div class="space-y-6">
    <!-- Header Info -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-gray-100 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
        <div>
            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Job ID</span>
            <p class="font-bold text-gray-900 dark:text-gray-100 text-lg">{{ $job->id_job }}</p>
        </div>
        <div>
            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Requester</span>
            <p class="font-bold text-gray-900 dark:text-gray-100">{{ $job->pengaju->name }}</p>
        </div>
        <div>
            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Current Status</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 capitalize">
                {{ str_replace('_', ' ', $job->status) }}
            </span>
        </div>
        <div>
            <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Current Dept</span>
            <p class="font-bold text-gray-900 dark:text-gray-100">
                {{ $job->latestRoute->toDepartment->department_name ?? 'N/A' }}
            </p>
        </div>
    </div>

    <!-- Description -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
        <h4 class="font-bold text-sm text-gray-700 dark:text-gray-300 mb-2 uppercase">Job Description</h4>
        <p class="text-gray-600 dark:text-gray-300 whitespace-pre-wrap text-sm">{{ $job->list_job }}</p>
    </div>

    <!-- Timeline History -->
    <div>
        <h4 class="font-bold text-lg text-gray-800 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2 mb-6">Activity History</h4>
        
        <div class="relative border-l-2 border-gray-300 dark:border-gray-600 ml-4 space-y-10">
            
            @foreach($activities as $activity)
                <div class="mb-8 ml-8 relative group">
                    <!-- Icon / Dot Indicator -->
                    <span class="absolute -left-[43px] flex items-center justify-center w-8 h-8 rounded-full ring-4 ring-white dark:ring-gray-800 
                        {{ $activity['type'] == 'route' ? 'bg-yellow-500' : 'bg-blue-600' }}">
                        @if($activity['type'] == 'route')
                            <!-- Icon Route (Panah/Swap) -->
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                        @else
                            <!-- Icon Note/Status (Pensil/Check) -->
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        @endif
                    </span>
                    
                    <div class="bg-white dark:bg-gray-700 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600 hover:shadow-md transition">
                        
                        <!-- Header Card Timeline -->
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                @if($activity['type'] == 'route')
                                    <h5 class="text-sm font-bold text-yellow-700 dark:text-yellow-400 uppercase tracking-wide">
                                        Department Transfer
                                    </h5>
                                    <div class="mt-1 text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                        <span>{{ $activity['data']->fromDepartment->department_name ?? 'Requester' }}</span>
                                        <svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                        <span>{{ $activity['data']->toDepartment->department_name }}</span>
                                    </div>
                                @else
                                    <h5 class="text-sm font-bold text-blue-700 dark:text-blue-400 uppercase tracking-wide">
                                        Status / Progress Update
                                    </h5>
                                    <!-- Jika user berada di Department tertentu saat update -->
                                    <p class="text-xs text-gray-500 mt-1">
                                        Department: {{ $activity['data']->route->toDepartment->department_name ?? ($job->latestRoute->toDepartment->department_name ?? 'Unknown') }}
                                    </p>
                                @endif
                            </div>
                            <span class="text-xs text-gray-400 bg-gray-100 dark:bg-gray-600 px-2 py-1 rounded">
                                {{ \Carbon\Carbon::parse($activity['timestamp'])->format('d M Y, H:i') }}
                            </span>
                        </div>
                        
                        <!-- Content Body (Note) -->
                        <div class="text-gray-700 dark:text-gray-300 text-sm bg-gray-50 dark:bg-gray-900/50 p-3 rounded border-l-4 {{ $activity['type'] == 'route' ? 'border-yellow-400' : 'border-blue-400' }}">
                            {{-- Tampilkan Newlines dengan benar --}}
                            {!! nl2br(e($activity['data']->note)) !!}
                        </div>

                        <!-- Attachments Section (Jika Ada) -->
                        @if($activity['files']->count() > 0)
                            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-600">
                                <p class="text-xs font-bold text-gray-500 mb-2">Attached Evidence / Files:</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($activity['files'] as $file)
                                        <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" 
                                           class="group relative flex items-center gap-2 px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                                           title="{{ $file->file_name }}">
                                            
                                            @php $ext = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION)); @endphp
                                            
                                            <!-- Icon based on extension -->
                                            @if(in_array($ext, ['jpg','jpeg','png','webp']))
                                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            @elseif($ext == 'pdf')
                                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                            @else
                                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            @endif

                                            <span class="text-xs truncate max-w-[150px] text-blue-600 dark:text-blue-400 underline">
                                                {{ $file->file_name }}
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        <!-- Footer User Info -->
                        <div class="mt-3 flex items-center justify-end text-xs text-gray-400">
                            <span class="mr-1">Action by:</span>
                            <span class="font-bold text-gray-600 dark:text-gray-300">
                                {{ $activity['creator']->name ?? 'System' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</div>