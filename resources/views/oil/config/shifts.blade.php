<x-app-layout>
    <div class="max-w-4xl mx-auto py-10 px-4">

        <!-- Header & Back Button -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="mdi mdi-clock-time-four-outline text-purple-600"></i> Shift Configuration
                </h2>
                <p class="text-sm text-gray-500">Define operational hours.</p>
            </div>
            <a href="{{ route('oil.config.center') }}"
                class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 font-medium transition flex items-center gap-2">
                <i class="mdi mdi-arrow-left"></i> Back to Center
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider">
                        <th class="p-4 font-semibold border-b">Shift Name</th>
                        <th class="p-4 font-semibold border-b">Start Time</th>
                        <th class="p-4 font-semibold border-b">End Time</th>
                        <th class="p-4 font-semibold border-b text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($shifts as $shift)
                        <tr class="hover:bg-gray-50 transition">
                            <form action="{{ route('oil.config.shifts.update', $shift->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <td class="p-4">
                                    <span class="font-bold text-gray-800">{{ $shift->name }}</span>
                                </td>
                                <td class="p-4">
                                    <input type="time" name="start_time"
                                        value="{{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}"
                                        class="border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm text-sm w-32">
                                </td>
                                <td class="p-4">
                                    <input type="time" name="end_time"
                                        value="{{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}"
                                        class="border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm text-sm w-32">
                                </td>
                                <td class="p-4 text-center">
                                    <button type="submit"
                                        class="px-4 py-2 bg-purple-600 text-white text-xs font-bold rounded hover:bg-purple-700 shadow-sm transition">
                                        <i class="mdi mdi-content-save"></i> SAVE
                                    </button>
                                </td>
                            </form>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Info Card -->
        <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-100 text-sm text-blue-700 flex items-start gap-3">
            <i class="mdi mdi-information text-xl"></i>
            <div>
                <strong>Note:</strong>
                System automatically detects overlapping times. Ensure Shift 3 ends where Shift 1 starts.
            </div>
        </div>

    </div>
</x-app-layout>