<x-app-layout>
    @section('title')
        Refinery Logs
    @endsection

    <div class="max-w-5xl mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-slate-700">Audit Logs</h2>
            <a href="{{ route('oil.batch_refinery.index') }}" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded text-sm font-bold">Back to Dashboard</a>
        </div>
        
        <div class="bg-white rounded-xl shadow overflow-hidden border border-slate-100">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-100 text-slate-600 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3">Timestamp</th>
                        <th class="px-6 py-3">Action</th>
                        <th class="px-6 py-3">User</th>
                        <th class="px-6 py-3">Details</th>
                        <th class="px-6 py-3">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($logs as $log)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3 text-slate-500 font-mono text-xs">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 rounded text-xs font-bold {{ str_contains($log->action, 'INPUT') ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-6 py-3 font-medium">{{ $log->user_id ?? 'System' }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $log->details }}</td>
                        <td class="px-6 py-3 text-slate-400 text-xs">{{ $log->ip_address }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</x-app-layout>