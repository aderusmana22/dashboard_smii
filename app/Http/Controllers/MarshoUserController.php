<?php

namespace App\Http\Controllers;

use App\Models\MarshoDepartment;
use App\Models\MarshoUser;
use App\Models\User;
use Illuminate\Http\Request;

class MarshoUserController extends Controller
{
    /**
     * Menampilkan view utama atau mengembalikan data JSON untuk AJAX.
     * Mampu menangani filtering pencarian dan paginasi.
     */
    public function index(Request $request)
    {
        // Departemen akan kita kirimkan pada initial load saja
        $marshoDepartments = MarshoDepartment::orderBy('department_name')->get();

        $query = User::query()
            ->with('marshoProfile.department') // Eager load relasi
            ->when($request->search, function ($q, $search) {
                return $q->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('name'); // Urutkan berdasarkan nama

        $users = $query->paginate(10);

        // Jika ini adalah request AJAX, kirim data paginasi sebagai JSON
        if ($request->expectsJson()) {
            return $users;
        }

        // Jika ini adalah request awal (load halaman), kirim ke view
        return view('resources.marsho_users.index', compact('users', 'marshoDepartments'));
    }

    /**
     * Menyimpan atau memperbarui penugasan departemen dan mengembalikan
     * profil pengguna yang diperbarui sebagai JSON.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'marsho_department_id' => 'nullable|exists:marsho_departments,id',
        ]);

        $message = '';
        $userId = $request->user_id;

        if (is_null($request->marsho_department_id) || $request->marsho_department_id === '') {
            MarshoUser::where('user_id', $userId)->delete();
            $message = 'User has been unassigned from Marsho system.';
        } else {
            MarshoUser::updateOrCreate(
                ['user_id' => $userId],
                ['marsho_department_id' => $request->marsho_department_id]
            );
            $message = 'User\'s Marsho department has been updated successfully.';
        }
        
        // Ambil kembali data user yang sudah diperbarui dengan relasinya
        $updatedUser = User::with('marshoProfile.department')->find($userId);

        return response()->json([
            'user' => $updatedUser,
            'message' => $message
        ], 200); // 200 OK
    }
}