<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respon Persetujuan Tugas - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Basic dark mode compatibility */
        @media (prefers-color-scheme: dark) {
            body { background-color: #1f2937; color: #d1d5db; }
            .card { background-color: #374151; border-color: #4b5563; }
            .card h1 { color: #f3f4f6; }
            .card p { color: #d1d5db; }
            .button { background-color: #4f46e5; hover:bg-indigo-700; }
            .button-secondary { background-color: #4b5563; hover:bg-gray-600; color: #f3f4f6; border-color: #6b7280; }
        }
    </style>
    <link rel="preconnect" href="https://rsms.me/">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-6">
    <div class="card max-w-lg w-full bg-white shadow-xl rounded-lg p-8 border border-gray-200 text-center">
        <div class="mb-6">
            @if(Str::contains(strtolower($message ?? ''), ['berhasil', 'disetujui', 'sukses']))
                <svg class="mx-auto h-16 w-16 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            @elseif(Str::contains(strtolower($message ?? ''), ['gagal', 'error', 'kadaluarsa', 'tidak valid', 'ditolak']))
                <svg class="mx-auto h-16 w-16 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            @else
                 <svg class="mx-auto h-16 w-16 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            @endif
        </div>
        <h1 class="text-2xl font-semibold text-gray-800 mb-4">
            @if(Str::contains(strtolower($message ?? ''), ['berhasil', 'disetujui', 'sukses']))
                Proses Berhasil
            @elseif(Str::contains(strtolower($message ?? ''), ['gagal', 'error', 'kadaluarsa', 'tidak valid', 'ditolak']))
                Proses Gagal atau Ditolak
            @else
                Informasi
            @endif
        </h1>
        <p class="text-gray-600 mb-8">
            {{ $message ?? 'Terjadi kesalahan yang tidak diketahui.' }}
        </p>
        <a href="{{ url('/') }}"
           class="button inline-block w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Kembali ke Beranda
        </a>
         @if (Auth::check())
            <a href="{{ route('page.kanban.index') }}"
               class="button-secondary mt-4 inline-block w-full sm:w-auto bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-lg shadow-md transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">
                Lihat Kanban Board
            </a>
        @endif
        <p class="mt-8 text-xs text-gray-500">
            &copy; {{ date('Y') }} {{ config('app.company_name', 'Perusahaan Anda') }}. All rights reserved.
        </p>
    </div>
</body>
</html>