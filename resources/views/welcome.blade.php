<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Milele SkillSage</title>
    @vite('resources/css/app.css')
</head>
<body class="antialiased font-sans">
    <div class="min-h-screen bg-gray-100 flex flex-col justify-center items-center">
        <img id="background" class="absolute h-screen w-screen bg-cover bg-center" src="{{ asset('images/bg.webp') }}" />
        <div class="relative min-h-screen flex flex-col items-center justify-center selection:text-white">
            <div class="w-[70%] md:w-[30%] mx-auto my-2 mb-16 block object-contain">
                <x-application-logo />
            </div>  
            <div class="grid grid-cols-2 gap-10">
                <!-- Login link -->
                <a href="{{ route('login') }}" class="text-white bg-blue-700 hover:bg-blue-600 font-bold py-2 px-4 rounded-lg text-sm md:text-base text-center focus:outline-none focus:shadow-outline">Log in</a>
                
                <!-- Register link -->
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="text-slate-900 bg-slate-200 hover:bg-slate-50 font-bold py-2 px-4 rounded-lg text-sm md:text-base text-center focus:outline-none focus:shadow-outline">Register</a>
                @endif
            </div>
            <!-- <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded">Logout</button>
            </form> -->
        </div>  
    </div>
</body>
</html>