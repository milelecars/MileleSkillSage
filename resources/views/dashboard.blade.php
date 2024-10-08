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
            <div class="grid grid-col gap-4">
                {{-- left --}}
                <div>
                    <h2>Camera Setup</h2>
                    <p>
                        We use camera images to ensure fairness for everyone.</br>
                        Make sure that you are in front of your camera.
                    </p>
                    
                </div>
                {{-- right --}}
                <div>
                    <div class="flex flex-row items-center gap-5 bg-warning p-6 mx-4 mb-4 overflow-hidden shadow-sm rounded">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                        <p class="text-sm text-justify">It seems you don't have a camera connected to your computer or your camera is blocked. To enable the camera, click on the camera blocked icon in your browser's address bar and reload the page. If you don't enable a camera, you can still take the assessment, but then Milele Motors cannot verify fair play.</p>
                    </div>
                    <div class="bg-info p-6 mx-4 mb-4 overflow-hidden shadow-sm rounded">
                        <p class="text-sm leading-9">
                            <strong>Trouble with your webcam?</strong></br>
                            Ensure you have granted permission for your browser to access your camera.</br>
                            Ensure you are using a <u>supported browser</u>.</br>
                            If you have multiple camera devices, ensure you have given your browser and our website permission to use the right device.</br>
                            Try launching the assessment in incognito mode or in a private window.</br>
                            Ensure your camera drivers and web browser are up to date.</br>
                            Restart your device and try accessing the assessment again using the link in the invitation email.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>