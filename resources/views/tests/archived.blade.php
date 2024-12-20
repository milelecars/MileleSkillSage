<x-app-layout>
    <div class="py-12 text-theme bg-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white shadow-lg border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center space-x-4">
                            <h1 class="text-2xl font-bold text-gray-800">Archived Tests</h1>
                        </div>
                    </div>
                    <div class="rounded-lg shadow-inner bg-gray-50">
                        <ul class="divide-y divide-gray-200">
                            @forelse ($archivedTests as $test)
                                <li class="p-4 hover:bg-gray-100 transition duration-150 ease-in-out rounded-lg">
                                    <div class="flex justify-between items-center">
                                        <div class="flex-grow">
                                            <h3 class="text-lg font-semibold text-gray-700">{{ $test->title }}</h3>
                                            <p class="text-base text-gray-600 mt-2">{{ Str::limit($test->description, 100) }}</p>
                                            <div class="flex items-center mt-4 text-sm text-gray-500">
                                                <span class="mr-4">Archived by: {{ $test->deletedBy->name }}</span>
                                                <span>Archived on: {{ $test->deleted_at->format('M d, Y') }}</span>
                                            </div>
                                        </div>        
                                        <div class="flex space-x-2">
                                            <form action="{{ route('tests.restore', $test->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-green-500 hover:text-green-600 p-2 rounded-full hover:bg-green-100 transition duration-150 ease-in-out" title="Restore Test">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="p-4 text-gray-500 text-center">No archived tests available.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>