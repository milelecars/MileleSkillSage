<x-app-layout>
    <div class="py-12 text-theme bg-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white shadow-lg border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-800">All Tests</h1>
                        @if(Auth::guard('web')->check())
                            <a href="{{ route('tests.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-300 disabled:opacity-25 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                Add New Test
                            </a>
                        @endif
                    </div>
                    <div class="rounded-lg shadow-inner bg-gray-50">
                        <ul class="divide-y divide-gray-200">
                            @forelse ($tests as $test)
                                <li class="p-4 hover:bg-gray-100 transition duration-150 ease-in-out rounded-lg">
                                    <div class="flex justify-between items-center">
                                        
                                        {{-- info --}}
                                        <a href="{{ route('tests.show', ['id' => $test->id]) }}">
                                            <h3 class="text-lg font-semibold text-blue-700">{{ $test->title }}</h3>
                                            <p class="text-sm text-gray-600 mt-2">{{ Str::limit($test->description, 100) }}</p>
                                        </a>
                                        
                                        {{-- edit & delete --}}
                                        <div class="flex space-x-2">
                                            <a href="{{ route('tests.edit', $test->id) }}" class="text-yellow-500 hover:text-yellow-600 p-2 rounded-full hover:bg-yellow-100 transition duration-150 ease-in-out">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                            </a>
                                            <form action="{{ route('tests.destroy', $test->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this test?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-600 p-2 rounded-full hover:bg-red-100 transition duration-150 ease-in-out">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="p-4 text-gray-500 text-center">No tests available.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>