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
            <img class="w-[30%] mb-20" src="https://www.milele.com/wp-content/themes/milene/images/MileleLogowhite.png" />
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="text-white bg-blue-700 hover:bg-blue-600 font-bold py-2 px-4 rounded-lg text-center">Dashboard</a>
                @else
                    <div class="grid grid-cols-2 gap-10">
                        <a href="{{ route('login') }}" class="text-white bg-blue-700 hover:bg-blue-600 font-bold py-2 px-4 rounded-lg text-center">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-slate-900 bg-slate-200 hover:bg-slate-50 font-bold py-2 px-4 rounded-lg text-center">Register</a>
                        @endif
                    </div>
                @endauth
            @endif
        </div>
    </div>
</body>
</html>