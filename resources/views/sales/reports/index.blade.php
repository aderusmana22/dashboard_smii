<x-app-layout>
    
    @section('title')
        Sales by Brand Report
    @endsection

    {{-- DataTables CSS --}}
    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    @endpush

    <style>
        /* For Tailwind CSS */
        .text-right {
            text-align: right !important;
        }
    </style>

    <div class="py-12">
        <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    {{-- Report Header --}}
                    <div class="p-4 text-center text-white rounded-lg shadow-md bg-gradient-to-r from-teal-400 to-blue-500 -mx-6 -mt-6 mb-6">
                        <h4 class="text-xl font-bold drop-shadow-md">Sales by Brand Report</h4>
                    </div>
                    
                    {{-- Filter Form --}}
                    <div class="flex flex-col sm:flex-row items-center justify-between mb-4 gap-4 p-3 border rounded-md bg-white shadow-sm">
                        <div class="flex flex-col sm:flex-row items-center gap-4">
                            <div>
                                <label for="start_month" class="block text-sm font-medium text-gray-700">Start Month</label>
                                <input type="month" id="start_month" value="{{ $startMonth }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="end_month" class="block text-sm font-medium text-gray-700">End Month</label>
                                <input type="month" id="end_month" value="{{ $endMonth }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </div>
                            <div class="mt-2 sm:mt-6">
                                <button type="button" id="apply-filter-btn" class="bg-blue-600 text-white font-medium px-4 py-2 text-sm rounded-md hover:bg-blue-700">
                                    Apply Filter
                                </button>
                            </div>
                        </div>
                        
                        <div class="mt-2 sm:mt-6">
                            <a href="#" id="export-excel-btn" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 flex items-center gap-2 text-sm no-underline">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Export to Excel
                            </a>
                        </div>
                    </div>

                    {{-- Data Table --}}
                    <div>
                        <table id="sales-report-table" class="w-full">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase">Brand</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase">Period</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase">Tonnage</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase">Value</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase">Margin</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase">%</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                {{-- Data is inserted here by DataTables --}}
                            </tbody>
                            <tfoot class="bg-gray-100 font-bold">
                                <tr>
                                    <th class="px-4 py-3 text-center" colspan="2">GRAND TOTAL</th>
                                    <th class="px-4 py-3 text-right"></th>
                                    <th class="px-4 py-3 text-right"></th>
                                    <th class="px-4 py-3 text-right"></th>
                                    <th class="px-4 py-3 text-right"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- jQuery and DataTables JS --}}
    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        
        <script>
            // Immediately-Invoked Function Expression (IIFE) to create a sandbox
            (function($) {
                // Use jQuery's noConflict mode to return control of the $ variable to other scripts. [7, 8, 15]
                // This is crucial if another version of jQuery or another library that uses $ is loaded.
                var dt_jQuery = $.noConflict(true);

                // Use the document ready event from our specific jQuery version
                dt_jQuery(document).ready(function() {
                    const formatNumber = (num) => {
                        const number = parseFloat(num);
                        if (isNaN(number)) {
                            return '0,00';
                        }
                        return number.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    };

                    const table = dt_jQuery('#sales-report-table').DataTable({
                        processing: true,
                        serverSide: false,
                        dom: '<"flex items-center justify-between mb-4"lf>rtip',
                        ajax: {
                            url: "{{ route('reports.sales.byBrand.data') }}",
                            data: function(d) {
                                d.start_month = dt_jQuery('#start_month').val();
                                d.end_month = dt_jQuery('#end_month').val();
                            }
                        },
                        columns: [
                            { data: 'brand', name: 'brand', className: 'dt-head-center dt-foot-right' },
                            { data: 'period', name: 'period', className: 'dt-head-center' },
                            { data: 'total_tonnage', name: 'total_tonnage', className: 'dt-body-right dt-head-center', render: formatNumber },
                            { data: 'total_value', name: 'total_value', className: 'dt-body-right dt-head-center', render: formatNumber },
                            { data: 'total_margin', name: 'total_margin', className: 'dt-body-right dt-head-center', render: formatNumber },
                            { 
                                data: null,
                                name: 'percentage',
                                className: 'dt-body-right dt-head-center',
                                render: function(data, type, row) {
                                    if (row.total_value > 0) {
                                        const percentage = (row.total_margin / row.total_value) * 100;
                                        return formatNumber(percentage) + '%';
                                    }
                                    return '0,00%';
                                }
                            }
                        ],
                        "footerCallback": function (row, data, start, end, display) {
                            const api = this.api();
                            const sumColumn = (colIndex) => api.column(colIndex, { page: 'current' }).data().reduce((a, b) => parseFloat(a) + parseFloat(b || 0), 0);
                            
                            const totalTonnage = sumColumn(2);
                            const totalValue = sumColumn(3);
                            const totalMargin = sumColumn(4);
                            const totalMarginPercentage = (totalValue > 0) ? (totalMargin / totalValue) * 100 : 0;

                            dt_jQuery(api.column(2).footer()).html(formatNumber(totalTonnage));
                            dt_jQuery(api.column(3).footer()).html(formatNumber(totalValue));
                            dt_jQuery(api.column(4).footer()).html(formatNumber(totalMargin));
                            dt_jQuery(api.column(5).footer()).html(formatNumber(totalMarginPercentage) + '%');
                        }
                    });

                    dt_jQuery('#apply-filter-btn').on('click', function() {
                        table.ajax.reload();
                    });

                    dt_jQuery('#export-excel-btn').on('click', function(e) {
                        e.preventDefault();
                        const startMonth = dt_jQuery('#start_month').val();
                        const endMonth = dt_jQuery('#end_month').val();
                        const exportUrl = `{{ route('reports.sales.byBrand.export') }}?start_month=${startMonth}&end_month=${endMonth}`; 
                        window.location.href = exportUrl;
                    });
                });
            })(jQuery);
        </script>
    @endpush

</x-app-layout>