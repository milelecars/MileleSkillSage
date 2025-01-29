<x-app-layout>
    <div class="text-theme" id="dashboard-container">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="p-6 pb-4 mt-5 mb-10 border-b-2 border-gray-800">
                <h1 class="text-3xl font-bold text-gray-900">
                    Report Unavailable
                </h1>
                <div class="text-sm text-gray-600 mt-2 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                    </svg>
                    {{ Auth::guard('candidate')->user()->email }}
                </div>
            </div>

            {{-- Content --}}
            <div class="grid grid-cols-1 gap-4 mx-4 pb-8">
                <div class="bg-white rounded-xl p-8 text-center shadow">
                    <h2 class="text-2xl font-bold text-red-600 mb-4">Oops!</h2>
                    <p class="text-gray-700 text-lg leading-relaxed">
                        {{ $errorMessage ?? 'No report is available at the moment. Please try again later.' }}
                    </p>
                    <div class="mt-6">
                        <a href="{{ url('/') }}" 
                           class="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Go Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
