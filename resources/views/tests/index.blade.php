<x-app-layout>
    <div class="py-12 text-theme">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold">All Tests</h1>
                        <a href="{{ route('tests.create') }}" class="text-blue-500 hover:text-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </a>
                    </div>
                    <div class="bg-white rounded p-5 text-theme">
                        <ul class="space-y-2">
                            @forelse ($tests as $test)
                                <li class="border-b pb-2">
                                    <a href="{{ route('tests.show', $test->id) }}" class="text-blue-700 hover:text-blue-600">
                                        {{ $test->name }}
                                    </a>
                                    <p class="text-sm text-gray-500 mt-3">{{ Str::limit($test->description, 100) }}</p>
                                </li>
                            @empty
                                <li class="text-gray-500">No tests available.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>