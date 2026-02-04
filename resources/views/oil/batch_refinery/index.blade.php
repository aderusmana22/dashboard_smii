<x-app-layout>
    @section('title')
        Batch Refinery Dashboard
    @endsection

    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-slate-700">Batch Refinery Dashboard</h2>
            <div class="flex gap-2">
                <a href="{{ route('oil.batch_refinery.input') }}" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700">
                    <i class="mdi mdi-play-circle"></i> Input Harian
                </a>
                <a href="{{ route('oil.batch_refinery.logs') }}" class="bg-slate-600 text-white px-4 py-2 rounded shadow hover:bg-slate-700">
                    <i class="mdi mdi-history"></i> Logs
                </a>
                <a href="{{ route('oil.batch_refinery.config.index') }}" class="bg-emerald-600 text-white px-4 py-2 rounded shadow hover:bg-emerald-700">
                    <i class="mdi mdi-cog"></i> Config
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ session('success') }}</p>
        </div>
        @endif

        <div class="card rounded-xl shadow-lg border border-slate-100 overflow-hidden mb-8">
            <div class="bg-slate-800 px-6 py-4 flex justify-between items-center">
                <h4 class="text-white font-semibold flex items-center gap-2">
                    Latest Snapshot
                    <span class="text-xs font-normal text-slate-400 ml-2" id="lastUpdateLabel">Update: -</span>
                </h4>
                <div class="flex gap-2">
                    <input type="date" id="dashStartDate" class="text-xs rounded border-0 p-1 text-slate-800">
                    <input type="date" id="dashEndDate" class="text-xs rounded border-0 p-1 text-slate-800">
                    <button onclick="loadDashboardData()" class="bg-blue-500 hover:bg-blue-400 text-white text-xs px-2 rounded">Go</button>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-white uppercase bg-slate-700 sticky top-0">
                        <tr>
                            <th class="px-4 py-3">Tank Code</th>
                            <th class="px-4 py-3 text-right">Capacity (Kg)</th>
                            <th class="px-4 py-3">Oil Code</th>
                            <th class="px-4 py-3">Description</th>
                            <th class="px-4 py-3 text-right">Gauge (M)</th>
                            <th class="px-4 py-3 text-right">Temp (°C)</th>
                            <th class="px-4 py-3 text-right">Current Value (Kg)</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="refineryTableBody" class="divide-y divide-slate-100 bg-white">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function loadDashboardData() {
        const start = document.getElementById('dashStartDate').value;
        const end = document.getElementById('dashEndDate').value;
        
        fetch(`{{ route('oil.batch_refinery.data') }}?start_date=${start}&end_date=${end}`)
            .then(r => r.json())
            .then(data => {
                const tbody = document.getElementById('refineryTableBody');
                tbody.innerHTML = '';
                
                const statusClass = {
                    'Hold': 'bg-yellow-100 text-yellow-800 border-yellow-200',
                    'Process': 'bg-blue-100 text-blue-800 border-blue-200',
                    'Release': 'bg-green-100 text-green-800 border-green-200',
                    'Reject': 'bg-red-100 text-red-800 border-red-200'
                };

                data.tableData.forEach(row => {
                    const sColor = statusClass[row.status] || 'bg-gray-100 text-gray-800';
                    const tr = `
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-bold text-slate-700">${row.tank_code}</td>
                            <td class="px-4 py-3 text-right font-mono text-slate-500">${row.capacity_kg}</td>
                            <td class="px-4 py-3">${row.oil_code}</td>
                            <td class="px-4 py-3">${row.description}</td>
                            <td class="px-4 py-3 text-right">${row.gauge_board}</td>
                            <td class="px-4 py-3 text-right">${row.temperature}</td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-600">${row.current_value}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded text-xs border font-semibold ${sColor}">${row.status}</span>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += tr;
                });
                
                if(data.tableData.length > 0) {
                    document.getElementById('lastUpdateLabel').innerText = 'Update: ' + data.tableData[0].last_update;
                }
            });
    }

    document.getElementById('dashEndDate').valueAsDate = new Date();
    document.getElementById('dashStartDate').valueAsDate = new Date(new Date().setDate(new Date().getDate() - 7));
    loadDashboardData();
    </script>
</x-app-layout>