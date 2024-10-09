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
            {{-- </div> --}}
            <div class="px-4 mb-10 border-b-2 border-sky-950">
                <div class="text-lg font-medium text-gray-900">
                    Welcome, {{ Auth::user()->name }}!
                </div>
                <div class="text-sm text-gray-500 mb-4">
                    {{ Auth::user()->email }}
                </div>
            </div>
           
            {{-- content --}}
            <div class="bg-white rounded mx-4 p-16 text-theme">
            hi
            </div>
        </div>
    </div>
</x-app-layout>