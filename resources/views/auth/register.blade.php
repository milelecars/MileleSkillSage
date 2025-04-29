<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="w-full max-w-sm mx-auto my-2">
        @csrf

        <div class="mb-4">
            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name</label>
            <input id="name" type="text" name="name" required autofocus autocomplete="name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
            <input id="email" 
                type="email" 
                name="email" 
                required 
                autocomplete="username" 
                class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <p id="emailError" class="text-red-500 text-xs hidden">Please enter a valid @milele.com email address.</p>
        </div>

        <!-- <div class="flex items-center justify-between mt-7">
            <button type="submit" class="bg-blue-700 hover:bg-blue-600 text-sm md:text-base text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline">
                Register
            </button>
            <a href="{{ route('login') }}" class="inline-block align-baseline font-bold  text-sm md:text-base text-theme hover:text-blue-600">
                Already registered?
            </a>
        </div> -->
    </form>
</x-guest-layout>

<script>
    document.getElementById('email').addEventListener('input', function () {
        const emailField = this;
        const emailError = document.getElementById('emailError');
        const emailPattern = /^[a-zA-Z0-9._%+-]+@milele\.com$/;

        if (!emailPattern.test(emailField.value)) {
            emailError.classList.remove('hidden');
            emailField.classList.add('border-red-500');
        } else {
            emailError.classList.add('hidden');
            emailField.classList.remove('border-red-500');
        }
    });

</script>