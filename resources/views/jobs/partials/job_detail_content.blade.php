<div class="space-y-6">
    <!-- Header Info -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-gray-100 p-4 rounded-lg dark:bg-gray-700">
        <div>
            <span class="text-xs text-gray-500 uppercase">Job ID</span>
            <p class="font-bold">{{ $job->id_job }}</p>
        </div>
        <div>
            <span class="text-xs text-gray-500 uppercase">Requester</span>
            <p class="font-bold">{{ $job->pengaju->name }}</p>
        </div>
        <div>
            <span class="text-xs text-gray-500 uppercase">Start Date</span>
            <p class="font-bold">{{ \Carbon\Carbon::parse($job->tanggal_job_mulai)->format('d M Y') }}</p>
        </div>
        <div>
            <span class="text-xs text-gray-500 uppercase">Deadline</span>
            <p class="font-bold {{ \Carbon\Carbon::parse($job->deadline)->isPast() ? 'text-red-500' : 'text-green-600' }}">
                {{ \Carbon\Carbon::parse($job->deadline)->format('d M Y') }}
            </p>
        </div>
    </div>

    <div>
        <h4 class="font-bold border-b pb-2 mb-4">Job Timeline & Evidence</h4>
        <div class="relative border-l-2 border-gray-200 dark:border-gray-600 ml-3 space-y-8">
            
            @foreach($activities as $activity)
                <div class="mb-8 ml-6 relative">
                    <!-- Dot Indicator -->
                    <span class="absolute -left-[33px] flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full ring-4 ring-white dark:ring-gray-800 dark:bg-blue-900">
                        <svg class="w-3 h-3 text-blue-800 dark:text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V5h2v4z"></path>
                        </svg>
                    </span>
                    
                    <div class="bg-white dark:bg-gray-700 p-4 rounded-lg shadow-sm border dark:border-gray-600">
                        <div class="flex justify-between items-start mb-2">
                            <h5 class="text-sm font-bold text-gray-900 dark:text-white">
                                @if($activity['type'] == 'route')
                                    Moved to: {{ $activity['data']->toDepartment->department_name }}
                                @else
                                    Status Update / Note
                                @endif
                            </h5>
                            <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($activity['timestamp'])->format('d M Y, H:i') }}</span>
                        </div>
                        
                        <p class="text-gray-600 dark:text-gray-300 mb-3 text-sm">
                            {{ $activity['data']->note }}
                        </p>

                        @if($activity['files']->count() > 0)
                            <div class="flex flex-wrap gap-2 mt-2">
                                @foreach($activity['files'] as $file)
                                    <a href="{{ Storage::url($file->file_path) }}" target="_blank" class="group relative block w-16 h-16 rounded overflow-hidden border">
                                        @if(in_array(pathinfo($file->file_name, PATHINFO_EXTENSION), ['jpg','jpeg','png','webp']))
                                            <img src="{{ Storage::url($file->file_path) }}" class="w-full h-full object-cover group-hover:opacity-75">
                                        @else
                                            <div class="flex items-center justify-center h-full bg-gray-100 text-xs text-gray-500">FILE</div>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        @endif
                        
                        <div class="mt-2 text-xs text-gray-400">
                            By: {{ $activity['data']->creator->name ?? 'System' }}
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</div>