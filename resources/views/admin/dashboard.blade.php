<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- <div class="flex items-center"> --}}
                {{-- <div class="flex-shrink-0 h-20 w-20">
                    @if (Auth::user()->profile_photo_path)
                        <img class="h-20 w-20 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">
                    @else
                        <div class="h-20 w-20 rounded-full bg-gray-300 flex items-center justify-center text-gray-500 text-2xl font-bold">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                    @endif
                </div> --}}
          
            <a href="{{ route('tests.index') }}" class="text-white bg-blue-700 hover:bg-blue-600 font-bold py-2 px-4 rounded text-center focus:outline-none focus:shadow-outline">Tests</a>
            
        </div>
    </div>
</x-app-layout>