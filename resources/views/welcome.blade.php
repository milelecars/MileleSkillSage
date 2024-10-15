<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to My App</title>
    @vite('resources/css/app.css')
</head>
<body class="antialiased font-sans">
    <div class="min-h-screen bg-gray-100 flex flex-col justify-center items-center">
        <img id="background" class="absolute h-screen w-screen bg-cover bg-center" src="{{ asset('images/bg.jpg') }}" />
        <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-[#FF2D20] selection:text-white">
            <a href="/" class="w-[30%] mx-auto my-2 mb-16 block object-contain">
                <x-application-logo />
            </a>            
            @auth
                @if(Auth::guard('web')->check())
                    <!-- Admin dashboard link -->
                    <a href="{{ route('admin.dashboard') }}" class="text-white bg-blue-700 hover:bg-blue-600 font-bold py-2 px-4 rounded text-center focus:outline-none focus:shadow-outline">Admin Dashboard</a>
                @elseif(Auth::guard('candidate')->check())
                    <!-- Candidate dashboard link -->
                    <a href="{{ route('candidate.dashboard') }}" class="text-white bg-blue-700 hover:bg-blue-600 font-bold py-2 px-4 rounded text-center focus:outline-none focus:shadow-outline">Candidate Dashboard</a>
                @else
                    <!-- Default dashboard link -->
                    <a href="{{ url('/dashboard') }}" class="text-white bg-blue-700 hover:bg-blue-600 font-bold py-2 px-4 rounded text-center focus:outline-none focus:shadow-outline">Dashboard</a>
                @endif
            @else
                <div class="grid grid-cols-2 gap-10">
                    <!-- Login link -->
                    <a href="{{ route('login') }}" class="text-white bg-blue-700 hover:bg-blue-600 font-bold py-2 px-4 rounded text-center focus:outline-none focus:shadow-outline">Log in</a>
                    
                    <!-- Register link -->
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="text-slate-900 bg-slate-200 hover:bg-slate-50 font-bold py-2 px-4 rounded text-center focus:outline-none focus:shadow-outline">Register</a>
                    @endif
                </div>
            @endauth
        

        </div>
    </div>
</body>
</html>