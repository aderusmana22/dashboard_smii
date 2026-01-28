@php
    $statusColors = [
        'to_be_scheduled' => 'bg-rose-100 text-rose-800',
        'scheduled'       => 'bg-sky-100 text-sky-800',
        'preparation'     => 'bg-purple-100 text-purple-800',
        'on_going'        => 'bg-amber-100 text-amber-800',
        'completed'       => 'bg-emerald-100 text-emerald-800',
        'closed'          => 'bg-cyan-100 text-cyan-800',
        'cancelled'       => 'bg-red-100 text-red-800',
        'default'         => 'bg-gray-100 text-gray-800',
    ];
    $currentStatusColor = $statusColors[$job->status] ?? $statusColors['default'];
@endphp

<div class="flex flex-col h-full bg-white rounded-lg">

    <div class="flex-shrink-0 p-6 space-y-4 border-b border-gray-200 z-10 bg-white rounded-t-lg">

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-gray-100 p-4 rounded-lg border border-gray-200">
            <div>
                <span class="text-xs text-gray-500 uppercase tracking-wider">Job ID</span>
                <p class="font-bold text-gray-500 text-lg">{{ $job->id_job }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-500 uppercase tracking-wider">Requester</span>
                <p class="font-bold text-gray-500">{{ $job->pengaju->name }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-500 uppercase tracking-wider">Current Status</span>

                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $currentStatusColor }} capitalize">
                    {{ str_replace('_', ' ', $job->status) }}
                </span>
            </div>
            <div>
                <span class="text-xs text-gray-500 uppercase tracking-wider">Current Dept</span>
                <p class="font-bold text-gray-500">
                    {{ $job->latestRoute->toDepartment->department_name ?? 'N/A' }}
                </p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-lg border border-gray-200">
            <h4 class="font-bold text-sm text-gray-700 mb-2 uppercase">Job Description</h4>
            <div class="max-h-24 overflow-y-auto custom-scrollbar">
                <p class="text-gray-600 whitespace-pre-wrap text-sm">{{ $job->list_job }}</p>
            </div>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto min-h-0 p-6 custom-scrollbar bg-gray-50/50 rounded-b-lg">

        <h4 class="font-bold text-lg text-gray-800 mb-6 border-b border-gray-200 pb-2">Activity History</h4>

        <div class="relative border-l-2 border-gray-300 ml-12 space-y-10 pb-4">
            @foreach($activities as $activity)
                <div class="mb-8 ml-6 relative group">
                    <span class="absolute -left-[50px] flex items-center justify-center w-8 h-8 rounded-full ring-4 ring-white shadow-sm {{ $activity['type'] == 'route' ? 'bg-yellow-500' : 'bg-blue-600' }}">
                        {{-- SVG Icons --}}
                        @if($activity['type'] == 'route') <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                        @else <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        @endif
                    </span>
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition w-full">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                @if($activity['type'] == 'route')
                                    <h5 class="text-sm font-bold text-yellow-700 uppercase tracking-wide">Department Transfer</h5>
                                    <div class="mt-1 text-sm font-semibold text-gray-500 flex items-center gap-2 flex-wrap">
                                        <span>{{ $activity['data']->fromDepartment->department_name ?? 'Requester' }}</span>
                                        <svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                        <span>{{ $activity['data']->toDepartment->department_name }}</span>
                                    </div>
                                @else
                                    <h5 class="text-sm font-bold text-blue-700 uppercase tracking-wide">Status / Progress Update</h5>
                                    <p class="text-xs text-gray-500 mt-1">Department: {{ $activity['data']->route->toDepartment->department_name ?? ($job->latestRoute->toDepartment->department_name ?? 'Unknown') }}</p>
                                @endif
                            </div>
                            <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded whitespace-nowrap ml-2">{{ \Carbon\Carbon::parse($activity['timestamp'])->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="text-gray-700 text-sm bg-gray-50 p-3 rounded border-l-4 {{ $activity['type'] == 'route' ? 'border-yellow-400' : 'border-blue-400' }}">
                            {!! nl2br(e($activity['data']->note)) !!}
                        </div>
                        @if($activity['files']->count() > 0)
                            <div class="mt-4 pt-3 border-t border-gray-100">
                                <p class="text-xs font-bold text-gray-500 mb-2">Attached Files:</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($activity['files'] as $file)
                                        @php
                                            $filePath = $file->file_path;
                                            $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                                            $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg']);
                                        @endphp
                                        @if($isImage)
                                            <button type="button" @click="$dispatch('open-modal', { imageUrl: '{{ asset('storage/' . $filePath) }}' })" class="flex items-center gap-2 px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">
                                                <span class="text-xs text-blue-600 underline truncate max-w-[150px]">{{ $file->file_name }}</span>
                                            </button>
                                        @else
                                            <a href="{{ asset('storage/' . $filePath) }}" target="_blank" class="flex items-center gap-2 px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">
                                                <span class="text-xs text-blue-600 underline truncate max-w-[150px]">{{ $file->file_name }}</span>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <div class="mt-3 flex items-center justify-end text-xs text-gray-400">
                            <span class="mr-1">Action by:</span>
                            <span class="font-bold text-gray-600">{{ $activity['creator']->name ?? 'System' }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div
    x-data="{ show: false, imageUrl: '' }"
    x-show="show"
    x-on:open-modal.window="show = true; imageUrl = $event.detail.imageUrl"
    x-on:keydown.escape.window="show = false"
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-white/50 backdrop-blur-md"
    style="display: none;"
>

    <div @click="show = false" class="absolute inset-0"></div>

    <button @click="show = false" class="absolute top-4 right-4 text-gray-800 bg-white/50 rounded-full p-2 hover:bg-white/80 transition">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
    </button>

    <div class="relative max-w-4xl max-h-[90vh] w-full">
        <img :src="imageUrl" alt="Image Preview" class="w-full h-full object-contain rounded-lg shadow-2xl">
    </div>
</div>