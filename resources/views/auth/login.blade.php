<x-guest-layout>
    {{-- Login Form --}}
    <form method="POST" action="{{ route('login') }}" class="w-full max-w-sm mx-auto my-2">
        @csrf


        @if (session('success'))
            <div class="alert alert-success mb-4 text-green-500">
                {{ session('success') }}
            </div>
        @endif

        <livewire:login-field />

        {{-- Submit Button --}}
        <div class="flex items-center justify-between mt-4">
            <button type="submit" class="bg-blue-700 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline">
                Log in
            </button>
        </div>
    </form>
</x-guest-layout>
