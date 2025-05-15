<x-guest-layout>
    @section('title')
    Reset Password
    @endsection

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="row m-0">
            <div class="col-12 p-0">
                <div class="login-card login-dark">
                    <div>
                    <div class="bg-white rounded10 shadow-lg">
                        <div class="content-top-agile px-20 pt-20 pb-0">
                            <h2 class="mb-10 text-2xl font-semibold text-primary">Reset Password ?</h2>
                            <p class="mb-0 text-fade">Enter your email to reset your password.</p>
                        </div>
                        <div class="px-20 pt-0 pb-20">
                            <form action="index.html" method="post">
                                <div class="form-group">
                                        <div class="relative w-full mt-4">
                                    <label for="email" class="block font-medium mb-2 text-xl">Email Address</label>
                                    <input type="email" name="email" id="email" class="border-1 py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400 dark:focus:ring-gray-600" placeholder="Example@smii.co.id">
                                </div>
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />

                                <!-- Password -->
                                <div class="form-group mt-4">
                                    <label for="password" class="block font-medium mb-2 text-xl">Password</label>
                                    <input type="password" name="password" id="password" class="border-1 py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400 dark:focus:ring-gray-600" required autocomplete="new-password">
                                </div>
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />

                                <!-- Confirm Password -->
                                <div class="form-group mt-4">
                                    <label for="password_confirmation" class="block font-medium mb-2 text-xl">Confirm Password</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="border-1 py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400 dark:focus:ring-gray-600" required autocomplete="new-password">
                                </div>
                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                            </form>
                        </div>
                        <div class="flex items-center justify-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Reset Password') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-guest-layout>
