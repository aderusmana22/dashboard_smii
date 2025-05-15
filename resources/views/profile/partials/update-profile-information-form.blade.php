<section>
    <header>
        <h1 class="text-xl font-medium text-gray-600">
            {{ __('Informasi Profil') }}
        </h1>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Perbarui informasi profil dan alamat email akun Anda.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.updates') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="nik" :value="__('Nik')" />
            <x-text-input id="nik" name="nik" type="text" class="mt-1 block w-full text-lg" :value="old('nik', $user->nik)" required autofocus autocomplete="nik" />
            <x-input-error class="mt-2" :messages="$errors->get('nik')" />
        </div>
        <div>
            <x-input-label for="name" :value="__('Nama')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full text-lg" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full text-lg" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Alamat email Anda belum diverifikasi.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Klik di sini untuk mengirim ulang email verifikasi.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('Tautan verifikasi baru telah dikirim ke alamat email Anda.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="avatar" :value="__('Avatar')" />
            @if ($user->avatar)
                <img id="avatar-preview" src="{{ Storage::url('public/user_avatars/' . $user->avatar) }}" alt="Avatar" class="mb-4" style="width: 100px; height: 100px;">
            @else
                <img id="avatar-preview" src="#" alt="Avatar" class="mb-4 hidden" style="width: 100px; height: 100px;">
            @endif
            <input type="file" name="avatar" id="avatar" class="mt-1 block w-full text-lg" onchange="previewAvatar()">
            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
        </div>

        <script>
            function previewAvatar() {
                var fileInput = document.getElementById('avatar');
                var file = fileInput.files[0];
                var reader = new FileReader();

                reader.onloadend = function () {
                    var img = document.getElementById('avatar-preview');
                    img.src = reader.result;
                    img.classList.remove('hidden');
                }

                if (file) {
                    reader.readAsDataURL(file);
                } else {
                    var img = document.getElementById('avatar-preview');
                    img.src = "";
                    img.classList.add('hidden');
                }
            }
        </script>

        <div class="flex items-center gap-4">
            <button class="btn btn-sm btn-primary">{{ __('Save') }}</button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Tersimpan.') }}</p>
            @endif
        </div>
    </form>
</section>
