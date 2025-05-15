<x-guest-layout>
    @section('title')
Forgot Password
    @endsection




    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="row m-0">
            <div class="col-12 p-0">
                <div class="login-card login-dark">
                    <div>
                    <div class="bg-white rounded10 shadow-lg">
                        <div class="content-top-agile px-20 pt-20 pb-0">
                            <h2 class="mb-10 text-2xl font-semibold text-primary">Forgot Password ?</h2>
                            <p class="mb-0 text-fade">Enter your email to reset your password.</p>
                        </div>
                         <!-- Session Status -->
                         <div class="px-20 pt-0 pb-20">
                            <x-auth-session-status class="" :status="session('status')" />
                            <form action="#" method="post">
                                <div class="form-group">
                                        <div class="relative w-full mt-4">
                                    <label for="input-label" class="block font-medium mb-2 dark:text-white text-xl">Email Address</label>
                                    <input type="email" name="email" id="input-label" class="border-1 py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400 dark:focus:ring-gray-600" placeholder="Example@smii.co.id">
                                </div>
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />

                                    <!-- <div class="input-group mb-3">
                                        <span class="input-group-text bg-transparent"><i class="text-fade ti-email"></i></span>
                                        <input type="email" class="form-control ps-15 bg-transparent" placeholder="Your Email">
                                    </div> -->
                                </div>
                                  <div class="row">
                                    <div class="col-12 text-center">
                                      <button type="submit" class="btn btn-primary w-p100 mt-10">Reset</button>
                                    </div>
                                    <!-- /.col -->
                                  </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-guest-layout>
