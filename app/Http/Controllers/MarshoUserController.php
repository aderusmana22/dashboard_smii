<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MarshoDepartment;
use App\Models\MarshoUser;
use App\Models\User;
use Illuminate\Http\Request;

class MarshoUserController extends Controller
{
    /**
     * Menampilkan halaman untuk mengelola departemen Marsho bagi setiap pengguna.
     */
    public function index()
    {
        // Ambil semua user dari sistem utama, dan eager load profil Marsho mereka
        $users = User::with('marshoProfile.department')
                     ->orderBy('name')
                     ->get();

        // Ambil semua departemen Marsho untuk dropdown
        $marshoDepartments = MarshoDepartment::orderBy('department_name')->get();

        return view('resources.marsho_users.index', compact('users', 'marshoDepartments'));
    }

    /**
     * Menyimpan atau memperbarui penugasan departemen Marsho untuk seorang pengguna.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'marsho_department_id' => 'nullable|exists:marsho_departments,id',
        ]);

        // Jika tidak ada departemen yang dipilih, hapus profil Marsho user tersebut
        if (is_null($request->marsho_department_id)) {
            MarshoUser::where('user_id', $request->user_id)->delete();
            return back()->with('success', 'User has been unassigned from Marsho system.');
        }

        // Gunakan updateOrCreate untuk membuat profil baru atau memperbarui yang sudah ada
        MarshoUser::updateOrCreate(
            ['user_id' => $request->user_id], // Kondisi pencarian
            ['marsho_department_id' => $request->marsho_department_id] // Data untuk diupdate/create
        );

        return back()->with('success', 'User\'s Marsho department has been updated successfully.');
    }
}
