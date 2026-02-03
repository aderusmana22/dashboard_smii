<x-app-layout>
    <div class="mx-auto py-8 px-4">

        <div class="flex items-center justify-between mb-8">
            <h2 class="text-3xl font-extrabold tracking-tight text-gray-800">
                History Logs
            </h2>

            <a href="{{ route('utility.gas.input') }}"
               class="inline-flex items-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition">
                ← Back to Input
            </a>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full w-full table-fixed text-sm">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-6 py-4 w-1/5 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                            Time
                        </th>
                        <th class="px-6 py-4 w-1/5 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                            User
                        </th>
                        <th class="px-6 py-4 w-2/5 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                            Item
                        </th>
                        <th class="px-6 py-4 w-1/5 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                            Change
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-700">
                                    {{ $log->created_at->format('d M Y H:i') }}
                                </div>
                                <div class="mt-1 text-[10px] text-gray-400">
                                    {{ $log->reading_date }}
                                </div>
                            </td>

                            <td class="px-6 py-4 font-semibold text-gray-900 truncate">
                                {{ $log->user_name }}
                            </td>

                            <td class="px-6 py-4 text-gray-600 truncate">
                                {{ $log->item_name }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-500 line-through">
                                        {{ $log->old_value ?? '-' }}
                                    </span>
                                    <span class="text-gray-300">→</span>
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-600">
                                        {{ $log->new_value }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center text-sm font-medium text-gray-400">
                                No history logs found ✨
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
