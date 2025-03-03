<x-app-layout>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md text-center">
            <h1 class="text-2xl font-bold text-red-600 mb-4">Test Suspended</h1>
            <p class="text-gray-700 mb-6">
                Your test has been suspended due to excessive <u>{{ $reason }}</u>.
            </p>
            <form action="{{ route('tests.request-unsuspension', $testId) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="description" class="block text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="description" rows="4" class="w-full p-2 border rounded" required></textarea>
                </div>
                <div class="mb-4">
                    <label for="evidence" class="block text-gray-700 mb-2">Attach Evidence</label>
                    <input 
                        type="file" 
                        name="evidence" 
                        id="evidence" 
                        class="w-full border rounded" 
                        accept="image/jpeg, image/png" 
                        required
                    >
                    <p class="text-sm text-gray-500 mt-2 flex">Only JPEG and PNG files are allowed.</p>                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 mt-5 rounded hover:bg-blue-700">
                    Request Unsuspension
                </button>
            </form>
        </div>
    </div>
</x-app-layout>