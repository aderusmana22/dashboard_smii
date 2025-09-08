<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Action Failed</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body style="background-image: url('{{ asset('frontend/assets/images/logo/pic.jpg') }}'); background-size: cover; background-position: center; background-repeat: no-repeat; height: 100vh;">
    <div class="w-full h-full flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white p-8 rounded-lg shadow-2xl text-center max-w-md mx-4">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mt-4">Failed!</h1>
            <p class="text-gray-600 mt-2">{{ $message }}</p>
            <p class="text-gray-500 mt-6 text-sm">Please try again or contact an administrator if the problem persists.</p>
            <div class="mt-6">
                <a href="{{ url('accidents-report') }}" style="display: inline-block; padding: 10px 20px; background-color: #2563eb; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 5px; transition: background-color 0.3s;" onmouseover="this.style.backgroundColor='#1d4ed8'" onmouseout="this.style.backgroundColor='#2563eb'">
                    Go to Accident Reports
                </a>
            </div>
        </div>
    </div>
</body>
</html>