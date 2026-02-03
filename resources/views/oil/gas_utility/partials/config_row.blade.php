<div class="p-4 flex justify-between items-center group hover:bg-gray-50 transition">
    <div class="flex-1">
        <div class="flex items-center gap-2">
            <h4 class="font-bold text-gray-800">{{ $item->name }}</h4>
            @if(!$item->is_active)
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-200 text-gray-500">INACTIVE</span>
            @endif
        </div>
        
        <div class="text-xs text-gray-500 mt-1 flex items-center gap-3">
            <span>Unit: <b>{{ $item->unit }}</b></span>
            <span class="text-gray-300">|</span>
            <span>Limit: <b>{{ $item->min_limit ?? 0 }} - {{ $item->max_limit ?? '∞' }}</b></span>
            <span class="text-gray-300">|</span>
            <span>Type: <b>{{ ucfirst($item->input_type) }}</b></span>
        </div>
    </div>
    
    <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
        <!-- Edit Button -->
        <button onclick='openModal("edit", @json($item))' 
                class="w-8 h-8 rounded bg-yellow-100 text-yellow-700 hover:bg-yellow-200 flex items-center justify-center"
                title="Edit Configuration">
            <i class="mdi mdi-pencil"></i>
        </button>

        <!-- Delete Button -->
        <form action="{{ route('utility.gas.config.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Are you sure? This will delete all historical data for this item!');">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    class="w-8 h-8 rounded bg-red-100 text-red-700 hover:bg-red-200 flex items-center justify-center"
                    title="Delete Item">
                <i class="mdi mdi-trash-can"></i>
            </button>
        </form>
    </div>
</div>