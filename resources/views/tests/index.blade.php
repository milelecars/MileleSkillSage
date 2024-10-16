<x-app-layout>
    <div class="py-12 text-theme">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold">All Tests</h1>
                        @if(Auth::guard('web')->check())
                            <a href="{{ route('tests.create') }}" class="text-blue-500 hover:text-blue-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </a>
                        @endif
                    </div>
                    <div class="bg-white rounded p-5 text-theme">
                        <ul class="space-y-4">
                            @forelse ($tests as $test)
                                <li class="border-b pb-4">
                                    <div class="flex justify-between items-center">
                                        <a href="{{ route('tests.show', $test->id) }}" class="text-blue-700 hover:text-blue-600 text-lg font-semibold">
                                            {{ $test->name }}
                                        </a>
                                        @if(Auth::guard('web')->check())
                                            <div class="flex space-x-2">
                                                <a href="{{ route('tests.edit', $test->id) }}" class="text-yellow-500 hover:text-yellow-600">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                    </svg>
                                                </a>
                                                <form action="{{ route('tests.destroy', $test->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this test?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-600">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-500 mt-2">{{ Str::limit($test->description, 100) }}</p>
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