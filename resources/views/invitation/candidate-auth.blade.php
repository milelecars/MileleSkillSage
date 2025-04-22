<x-guest-layout>
    <div class="max-w-md mx-auto my-6 px-4">
        <div class="mb-6">
            <h2 class="text-lg md:text-2xl font-bold text-gray-800">Test Invitation</h2>
            <p class="text-sm md:text-base text-gray-600 mt-2">Please enter your details to access the test.</p>
        </div>

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-3 sm:px-4 py-2 sm:py-3 rounded-lg relative mb-4 text-sm sm:text-base" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('invitation.validate', ['token' => $invitation->invitation_token]) }}" method="POST">
            @csrf

            <div class="mb-3 sm:mb-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Full Name</label>
                <input id="name" 
                       type="text" 
                       name="name" 
                       value="{{ old('name') }}" 
                       required 
                       autofocus 
                       class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-3 sm:mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                <input id="email" 
                       type="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
                       class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>

            <input type="hidden" name="invitation_token" value="{{ $invitation->invitation_token }}">

            <div class="flex items-center justify-center mt-6">
                <button type="submit" 
                        class="bg-blue-700 hover:bg-blue-600 text-sm md:text-base text-white font-bold py-2 px-6 rounded-lg focus:outline-none focus:shadow-outline">
                    Enter
                </button>
            </div>
        </form>
        
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mt-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
    </div>
</x-guest-layout>