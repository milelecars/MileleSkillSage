
<form method="POST" action="{{ route('login') }}" class="w-full max-w-sm mx-auto my-2">
    @csrf


    @if (session('success'))
        <div class="alert alert-success mb-4 text-green-500">
            {{ session('success') }}
        </div>
    @endif

    {{-- Email Input --}}
    <div class="mb-4">
        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
        <input 
            id="email" 
            name="email"
            type="email" 
            wire:model="email" 
            required 
            autocomplete="username" 
            class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
        >
    </div>

    <div class="mb-6">
        <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
        <input 
            id="password" 
            type="password" 
            name="password" 
            required 
            autocomplete="current-password" 
            class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
        >
    </div>


    {{-- OTP Input --}}
    <div class="mb-1">
        <label for="otp" class="block text-gray-700 text-sm font-bold mb-2">OTP</label>
        <div class="flex">
            <input 
                id="otp" 
                type="text" 
                name="OTP"
                wire:model="otp" 
                placeholder="Enter OTP sent to your email"
                required 
                autocomplete="one-time-code" 
                class="appearance-none border text-sm rounded-l-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <button 
                type="button" 
                wire:click="generateOtp" 
                class="text-white border border-neutral-500 bg-theme font-bold px-3 rounded-r-lg focus:outline-none focus:shadow-outline text-xs flex items-center">
                <span>{{ $otpGenerated ? 'Resend' : 'Generate' }}</span>
            </button>
        </div>
    </div>

    {{-- Success and Error Messages --}}
    @if ($errorMessage)
        <p class="text-red-500 text-xs">{{ $errorMessage }}</p>
    @endif
    @if ($successMessage)
        <p class="text-green-500 text-xs">{{ $successMessage }}</p>
    @endif



    {{-- Submit Button --}}
    <div class="flex items-center justify-between mt-7">
        <button type="submit" class="bg-blue-700 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Log in
        </button>
        
        <a href="{{ route('register') }}" class="inline-block align-baseline font-bold text-sm text-theme hover:text-blue-600">
            Need an account?
        </a>
    </div>
</from>