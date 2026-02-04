<x-app-layout>
    @section('title')
        Refinery Configuration
    @endsection

    <div class="max-w-5xl mx-auto p-6">
        <h2 class="text-2xl font-bold text-slate-700 mb-6">Tank Configuration</h2>

        <div class="bg-white p-6 rounded-xl shadow mb-8 border border-slate-100">
            <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2"><i class="mdi mdi-plus-box"></i> Add New Tank</h4>
            <form action="{{ route('oil.batch_refinery.config.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
                @csrf
                <div class="md:col-span-1">
                    <label class="block text-xs font-bold text-slate-500 mb-1">Group Seq</label>
                    <select name="group_name" class="w-full rounded border-slate-300 text-sm" required>
                        <option value="Hydro">1. Hydro</option>
                        <option value="N.W.B">2. N.W.B</option>
                        <option value="Deodorizer">3. Deodorizer</option>
                        <option value="Drop Tank">4. Drop Tank</option>
                        <option value="Wead Tank">5. Wead Tank</option>
                        <option value="Crystalizer">6. Crystalizer</option>
                        <option value="SX Tank">7. SX Tank</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 mb-1">Tank Name</label>
                    <input type="text" name="name" placeholder="Name" class="w-full rounded border-slate-300 text-sm" required>
                </div>
                <div class="md:col-span-1">
                    <label class="block text-xs font-bold text-slate-500 mb-1">Code</label>
                    <input type="text" name="code" placeholder="Unique" class="w-full rounded border-slate-300 text-sm" required>
                </div>
                <div class="md:col-span-1">
                    <label class="block text-xs font-bold text-slate-500 mb-1">Cap (Kg)</label>
                    <input type="number" name="capacity_kg" placeholder="0" class="w-full rounded border-slate-300 text-sm" required>
                </div>
                <div class="md:col-span-1">
                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white rounded py-2 text-sm font-bold">Add</button>
                </div>
                <input type="hidden" name="sort_order" value="0">
            </form>
        </div>

        <div class="bg-white rounded-xl shadow border border-slate-100 overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-100 text-slate-600 uppercase text-xs font-bold">
                    <tr>
                        <th class="px-4 py-3">Group</th>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Capacity</th>
                        <th class="px-4 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($tanks as $tank)
                    <tr>
                        <form action="{{ route('oil.batch_refinery.config.update', $tank->id) }}" method="POST">
                            @csrf @method('PUT')
                            <td class="px-4 py-2">
                                <select name="group_name" class="text-xs rounded border-slate-200 bg-slate-50">
                                    @foreach(['Hydro','N.W.B','Deodorizer','Drop Tank','Wead Tank','Crystalizer','SX Tank'] as $g)
                                        <option value="{{$g}}" {{$tank->group_name == $g ? 'selected' : ''}}>{{$g}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-2"><input type="text" name="name" value="{{$tank->name}}" class="text-xs rounded border-slate-200 w-full"></td>
                            <td class="px-4 py-2 text-slate-500 font-mono">{{$tank->code}}</td>
                            <td class="px-4 py-2"><input type="number" name="capacity_kg" value="{{$tank->capacity_kg}}" class="text-xs rounded border-slate-200 w-24"></td>
                            <td class="px-4 py-2 flex justify-center gap-2">
                                <button type="submit" class="text-blue-600 hover:text-blue-800 bg-blue-50 p-1 rounded"><i class="mdi mdi-content-save"></i></button>
                                <button type="button" class="text-red-600 hover:text-red-800 bg-red-50 p-1 rounded" onclick="if(confirm('Delete?')) document.getElementById('del-{{$tank->id}}').submit()"><i class="mdi mdi-delete"></i></button>
                            </td>
                        </form>
                        <form id="del-{{$tank->id}}" action="{{ route('oil.batch_refinery.config.destroy', $tank->id) }}" method="POST" class="hidden">@csrf @method('DELETE')</form>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>