<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reject Accident Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body style="background-image: url('{{ asset('frontend/assets/images/logo/pic.jpg') }}'); background-size: cover; background-position: center; background-repeat: no-repeat; min-height: 100vh;">
    <div class="w-full min-h-screen flex items-center justify-center bg-black bg-opacity-50 py-8">
        <div class="bg-white p-8 rounded-lg shadow-2xl w-full max-w-lg mx-4">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Reject Report</h1>
            <p class="text-gray-600 mb-6">Please provide a clear reason why this report is being rejected.</p>
            
            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <p class="font-bold">Error</p>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('email-approval.reject') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div>
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Reason for Rejection (min. 10 characters)</label>
                    <textarea id="rejection_reason" name="rejection_reason" rows="5" required minlength="10" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g., The incident description is not detailed enough, please add a more complete chronology...">{{ old('rejection_reason') }}</textarea>
                </div>
                <div class="mt-6">
                    <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Submit Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>