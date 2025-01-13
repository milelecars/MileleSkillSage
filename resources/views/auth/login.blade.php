<x-guest-layout>
    {{-- Login Form --}}
    <form method="POST" action="{{ route('login') }}" class="w-full max-w-sm mx-auto my-2">
        @csrf

        {{-- Email Field --}}
        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
            <input 
                id="email" 
                type="email" 
                name="email" 
                value="{{ old('email') }}"
                required 
                autofocus 
                autocomplete="username" 
                class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline
                    @error('email') border-red-500 @enderror"
            >
            @error('email')
                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
            @enderror
        </div>
       
        {{-- Password Field --}}
        <div class="mb-6">
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
            <input 
                id="password" 
                type="password" 
                name="password" 
                required 
                autocomplete="current-password" 
                class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline
                    @error('password') border-red-500 @enderror"
            >
            @error('password')
                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
            @enderror
        </div>
       
        {{-- OTP Field --}}
        <div class="mb-6">
            <label for="OTP" class="block text-gray-700 text-sm font-bold mb-2">OTP</label>
            <div class="flex">
                <input 
                    id="OTP" 
                    type="text" 
                    name="OTP" 
                    placeholder="Enter OTP sent to your email"
                    required 
                    autocomplete="one-time-code" 
                    class="appearance-none border border-r-0 text-sm rounded-l-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('OTP') border-red-500 @enderror"
                >
                
                <button 
                    type="button" 
                    id="otp-button"
                    onclick="handleOtpAction()" 
                    class="text-white border border-neutral-500 border-l-0 bg-theme font-bold px-3 rounded-r-lg focus:outline-none focus:shadow-outline text-xs flex items-center">
                    {{-- Text Label --}}
                    <span id="otp-label">{{ session('otp_generated') ? 'Resend' : 'Generate' }}</span>
                    
                    {{-- Regenerate Icon --}}
                    <svg id="regenerate-icon" xmlns="http:
                        class="{{ session('otp_generated') ? '' : 'hidden' }} h-4 w-4 ml-1" 
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
            </div>
            @error('OTP')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
            @if (session('error'))
                <p class="text-red-500 text-xs mt-1">{{ session('error') }}</p>
            @endif
            @if (session('success'))
                <p class="text-green-500 text-xs mt-1">{{ session('success') }}</p>
            @endif
        </div>

        {{-- Authentication Errors --}}
        @if ($errors->has('authentication'))
            <div class="mb-4 text-red-500 text-sm">
                {{ $errors->first('authentication') }}
            </div>
        @endif

        {{-- Buttons --}}
        <div class="flex items-center justify-between">
            {{-- Submit Button --}}
            <button type="submit" class="bg-blue-700 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline">
                Log in
            </button>
           
            <a href="{{ route('register') }}" class="inline-block align-baseline font-bold text-sm text-theme hover:text-blue-600">
                Need an account?
            </a>
        </div>
    </form>

    {{-- Hidden Resend OTP Form --}}
    <form id="generate-otp-form" method="POST" action="{{ route('generate.otp') }}" style="display: none;">
        @csrf
        <input type="hidden" name="email" id="otp-email-field">
    </form>

    <script>
        function handleOtpAction() {
            const emailField = document.getElementById('email');
            document.getElementById('otp-email-field').value = emailField.value;
            
            
            const otpLabel = document.getElementById('otp-label');
            const regenerateIcon = document.getElementById('regenerate-icon');
            otpLabel.textContent = 'Resend';
            regenerateIcon.classList.remove('hidden');
            
            
            document.getElementById('generate-otp-form').submit();
        }
    </script>
</x-guest-layout>