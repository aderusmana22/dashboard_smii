<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use RealRashid\SweetAlert\Facades\Alert;


class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view user', ['only' => ['index']]);
        $this->middleware('permission:create user', ['only' => ['create', 'store']]);
        $this->middleware('permission:update user', ['only' => ['update', 'edit']]);
        $this->middleware('permission:updateProfile user', ['only' => ['updateProfile']]);
        $this->middleware('permission:delete user', ['only' => ['destroy']]);
    }

    public function index(User $user)
    {
        $users = User::get();
        $positions = Position::all();
        $departments = Department::all();
        $roles = Role::pluck('name', 'name')->all();
        $userRoles = $user->roles->pluck('name', 'name')->all();
        return view('roleuser.user.index', \compact('users', 'positions', 'roles', 'userRoles', 'departments'));
    }

    public function create()
    {
        $roles = Role::pluck('name', 'name')->all();
        return view('roleuser.user.create', ['roles' => $roles]);
    }

    public function store(Request $request)
    {
        // \dd($request->all());
        $request->validate([
            'nik' => 'required|min:4|max:6|unique:users,nik',
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|max:20',
            'roles' => 'required'
        ]);

        $user = User::create([
            'nik' => $request->nik,
            'username' => $request->username,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'position_id' => $request->position_id,
            'department_id' => $request->department_id
        ]);

        $user->syncRoles($request->roles);

        Alert::toast('User created successfully with roles!', 'success');
        return redirect()->route('users.index');
    }

    public function edit(User $user)
    {
        $positions = Position::all();
        $department = Department::all();
        $roles = Role::pluck('name', 'name')->all();
        $userRoles = $user->roles->pluck('name', 'name')->all();
        return view('roleuser.user.edit', [
            'user' => $user,
            'roles' => $roles,
            'userRoles' => $userRoles
        ], compact('positions', 'department'));
    }

    public function update(Request $request, User $user)
    {
        // Validate the request data
        $request->validate([
            'email' => 'required|email|max:255' . $user->id,
            'nik' => 'required|min:4|max:6|unique:users,nik,' . $user->id,
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:8|max:20',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'required|in:active,non active',
            'position_id' => 'required|exists:positions,id',
            'department_id' => 'required|exists:departments,id',
            'passwordsim' => 'nullable|string|min:8|max:20',
        ]);

        // Prepare the data for update
        $data = $request->only([
            'nik',
            'username',
            'name',
            'email',
            'position_id',
            'department_id',
            'status',
            'passwordsim'
        ]);

        // Update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if it exists
            if ($user->avatar) {
                Storage::disk('public')->delete('user_avatars/' . $user->avatar);
            }

            // Get original extension
            $extension = $request->avatar->getClientOriginalExtension();

            // Store new avatar with original extension
            $avatarName = $request->username . '.' . $extension;
            $path = $request->avatar->storeAs('user_avatars', $avatarName, 'public');

            // Log path for debugging
            Log::channel('custom')->info('Avatar stored at path: ' . $path);
            Log::channel('custom')->info('Storage directory contents:', Storage::disk('public')->allFiles('user_avatars'));

            // Verify that file exists
            if (!Storage::disk('public')->exists('user_avatars/' . $avatarName)) {
                Log::error('Failed to store avatar: ' . $avatarName);
            }

            $data['avatar'] = $avatarName;
        }

        // Update user data
        $user->update($data);

        // Sync user roles
        $user->syncRoles($request->roles);

        // Flash success message and redirect
        Alert::toast('User updated successfully with roles!', 'success');
        return redirect()->route('users.index');
    }



    public function destroy($userId)
    {
        $user = User::findOrFail($userId);

        // Hapus avatar jika ada
        if ($user->avatar) {
            Storage::delete('public/user_avatars/' . $user->avatar);
        }

        $user->delete();

        Alert::toast('User deleted successfully!', 'success');
        return redirect()->route('users.index');
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'nik' => 'required|min:4|max:6|unique:users,nik,' . $request->user()->id,
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255' . $request->user()->id,
            'password' => 'nullable|string|min:8|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $user = $request->user();
        $user->nik = $request->nik;
        $user->name = $request->name;
        $user->email = $request->email;

        // Update password if provided
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if it exists
            if ($user->avatar) {
                Storage::disk('public')->delete('user_avatars/' . $user->avatar);
            }

            // Get original extension
            $extension = $request->avatar->getClientOriginalExtension();

            // Store new avatar with username
            $avatarName = $user->username . '.' . $extension;
            $path = $request->avatar->storeAs('user_avatars', $avatarName, 'public');

            // Log path for debugging
            Log::channel('custom')->info('Avatar stored at path: ' . $path);
            Log::channel('custom')->info('Storage directory contents:', Storage::disk('public')->allFiles('user_avatars'));

            // Verify that file exists
            if (!Storage::disk('public')->exists('user_avatars/' . $avatarName)) {
                Log::error('Failed to store avatar: ' . $avatarName);
            }

            $user->avatar = $avatarName;
        }

        $user->save();

        Alert::toast('Profile updated successfully!', 'success');
        return redirect()->route('profile.edit');
    }

    public function getCsrfToken()
    {
        $cookieJar = new CookieJar();

        // Ambil token CSRF dari halaman login
        $csrfResponse = Http::withOptions(['cookies' => $cookieJar, 'allow_redirects' => true])
            ->get('http://192.100.150.7:8001/');

        Log::info('Respon CSRF:', ['body' => $csrfResponse->body()]);

        // Ekstrak token CSRF dari respons
        preg_match('/name="csrfmiddlewaretoken" value="([^"]+)"/', $csrfResponse->body(), $matches);
        $csrfToken = $matches[1] ?? null;

        if (!$csrfToken) {
            return response()->json(['status' => 'error', 'message' => 'Gagal mendapatkan token CSRF.'], 500);
        }

        return response()->json(['csrfToken' => $csrfToken, 'cookies' => $cookieJar->toArray()]);
    }

    public function loginToPython()
    {
        $user = Auth::user(); // Ambil data pengguna yang sedang login

        try {
            // Step 1: Lakukan GET untuk mengambil halaman login
            $loginPageResponse = Http::withOptions(['allow_redirects' => true])
                ->get('http://192.100.150.7:8001/loginAuth?next=/');

            if ($loginPageResponse->failed()) {
                Log::error('Gagal mengambil halaman login.', ['status' => $loginPageResponse->status()]);
                return response()->json(['status' => 'error', 'message' => 'Gagal mengambil halaman login.'], 400);
            }

            // Step 2: Ekstrak CSRF token dari respons HTML
            preg_match('/name="csrfmiddlewaretoken" value="(.*?)"/', $loginPageResponse->body(), $matches);
            $csrfToken = $matches[1] ?? null;

            if (!$csrfToken) {
                Log::error('Gagal mengekstrak CSRF token.');
                return response()->json(['status' => 'error', 'message' => 'Gagal mengekstrak CSRF token.'], 400);
            }

            // Step 3: Ambil cookies dari respons
            $cookies = $loginPageResponse->cookies(); // Ambil cookies dari respons

            if (empty($cookies)) {
                Log::error('Gagal mendapatkan cookies.');
                return response()->json(['status' => 'error', 'message' => 'Gagal mendapatkan cookies.'], 400);
            }

            // Step 4: Ubah cookies menjadi format yang diharapkan oleh Guzzle
            $cookieArray = [];
            foreach ($cookies as $cookie) {
                $cookieArray[$cookie->getName()] = $cookie->getValue();
            }

            // Inisialisasi CookieJar dengan cookies yang diberikan
            $cookieJar = CookieJar::fromArray($cookieArray, '192.100.150.7');

            // Step 5: Siapkan data login
            $postData = [
                'username' => $user->username,
                'password' => $user->passwordsim, // Asumsikan password pengguna tersimpan di kolom passwordsim
                'csrfmiddlewaretoken' => $csrfToken,
            ];

            Log::info('Posting data ke Python:', $postData);

            // Step 6: Kirim permintaan POST dengan data login
            $response = Http::withOptions(['cookies' => $cookieJar])
                ->withHeaders([
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Referer' => 'http://192.100.150.7:8001/loginAuth',
                    'X-CSRFToken' => $csrfToken,
                ])
                ->asForm() // Format data sebagai form-urlencoded
                ->post('http://192.100.150.7:8001/loginAuth?next=/', $postData);

            Log::info('Response:', ['body' => $response->body()]);

            // Step 7: Periksa apakah login berhasil
            if ($response->successful()) {
                return response()->json(['status' => 'success']);
            } else {
                Log::error('Gagal login ke aplikasi Python.', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal login ke aplikasi Python. Error: ' . $response->status()
                ], 401);
            }
        } catch (\Exception $e) {
            Log::error('Terjadi kesalahan saat mencoba login ke aplikasi Python.', [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan tak terduga. Silakan coba lagi.'
            ], 500);
        }
    }

    public function getDataMaster()
    {
        return \view('page.wsa-getmstr');
    }
}
