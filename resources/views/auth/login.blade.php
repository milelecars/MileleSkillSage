<x-guest-layout>
    <form method="POST" action="{{ route('login') }}" class="w-full max-w-sm mx-auto my-2">
        @csrf

        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
            <input id="email" type="email" name="email" required autofocus autocomplete="username" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
       
        <div class="mb-6">
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-700 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Log in
            </button>
           
            <a href="{{ route('register') }}" class="inline-block align-baseline font-bold text-sm text-theme hover:text-blue-600">
                Need an account?
            </a>
        </div>
    </form>
</x-guest-layout>