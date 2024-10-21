<x-app-layout>
    <div class="py-12 text-theme">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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

                {{-- header --}}
                <div class="p-6 pb-4 mb-10 border-b-2 border-gray-800">
                    <h1 class="text-2xl font-bold text-gray-800">
                        Welcome, {{ Auth::guard('candidate')->user()->name}} !
                    </h1>
                    <div class="text-sm text-gray-500 mt-1">
                        {{ Auth::guard('candidate')->user()->email }}
                    </div>
                </div>


                {{-- content --}}
                <div class="grid grid-cols-2 gap-4 mx-4">
                    {{-- left --}}
                    <div>
                        <h2>Camera Setup</h2>
                        <p>
                            We use camera images to ensure fairness for everyone.</br>
                            Make sure that you are in front of your camera.
                        </p>

                        {{-- <x-webcam videoElementId="webcam"></x-webcam> --}}
                    </div>
                    {{-- right --}}
                    <div>
                        <div class="flex items-center gap-5 bg-warning p-5 mb-4 overflow-hidden shadow-sm rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-24 h-24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                            <p class="text-sm text-justify ">It seems you don't have a camera connected to your computer or your camera is blocked. To enable, click on the camera blocked icon in your browser's address bar and reload the page. If you don't enable a camera, you can still take the assessment, but then Milele Motors cannot verify fair play.</p>
                        </div>
                        <div class="bg-info p-6 mb-4 overflow-hidden shadow-sm rounded-lg">
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

                        @if(isset($test))
                            <div class="mb-6">
                                @if(isset($testStatus) && $testStatus && $testStatus->pivot->completed_at)
                                    {{-- Test completed --}}
                                    <div class="flex justify-between items-center bg-green-100 border-l-4 border-green-500 rounded-lg p-4 mb-4">
                                        <p class="text-green-700">You have completed this test.</p>
                                        <a href="{{ route('tests.result', ['id' => $test->id]) }}"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                            View Results
                                        </a>
                                    </div>
                                @elseif(isset($testStatus) && $testStatus && $testStatus->pivot->started_at)
                                    {{-- Test in progress --}}
                                    <div class="flex justify-between items-center bg-blue-100 border-l-4 border-blue-500 rounded-lg p-4 mb-4">
                                        <p class="text-blue-700">You have a test in progress.</p>
                                        <a href="{{ route('tests.start', ['id' => $test->id]) }}"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                            Continue Test
                                        </a>
                                    </div>
                                @else
                                    {{-- Test not started --}}
                                    <div class="flex justify-between items-center bg-gray-100 border-l-4 border-gray-500 rounded-lg p-4 mb-4">
                                        <p class="text-gray-700">Please review the guidelines before starting the test.</p>
                                        <a href="{{ route('tests.show', ['id' => $test->id]) }}"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                            View Guidelines
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4">
                                <p class="text-yellow-700">No test is currently available. Please check your invitation or contact the administrator.</p>
                            </div>
                        @endif
                    </div>
                </div>
        </div>
    </div>
</x-app-layout>