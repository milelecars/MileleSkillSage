<x-app-layout>
    <div class="text-theme" id="dashboard-container">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-4 mx-4 mt-5 pb-8">
                <div class="bg-white rounded-xl p-8 text-center shadow">
                    <h2 class="text-2xl font-bold text-red-600 mb-4">Oops!</h2>
                    <p class="text-gray-700 text-lg leading-relaxed">
                        {{ $errorMessage ?? 'No report is available at the moment. Please try again later.' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
