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
            <div class="px-2 mb-10 border-b-2 border-sky-950">
                <h1 class="text-2xl font-bold">
                    Welcome, {{ Auth::user()->name }}!
                </h1>
                <div class="text-sm text-gray-500 mb-4">
                    {{ Auth::user()->email }}
                </div>
            </div>
           
            {{-- content --}}
            <div class="bg-white rounded mx-4 p-16 text-theme">
                <h1 class="text-3xl font-extrabold mb-6">AGCT Test</h1>
                <p class="text-lg mb-16 text-justify">
                    You are about to begin the AGCT online cognitive test, consisting of <u>150 multiple-choice questions</u>. The test is designed to assess a wide range of cognitive abilities and must be completed in one sitting. Please carefully read the following important guidelines before starting the test.
                </p>
                
                <div class="flex gap-2 items-center my-6">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#AA2E26" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                      </svg>
                      
                      <h2 class="text-xl font-extrabold text-red-700">Important Guidelines</h2>
                </div>
                <ul class="text-lg text-justify list-disc pl-6">
                    <li class="mb-4"><strong>Test Duration:</strong> Once you start the test, a timer will begin and continue to run even if you leave the browser or close the window. Make sure you have adequate time to complete all questions in one session.</li>
                    <li class="mb-4"><strong>Email Requirement:</strong> You must use the same email address throughout the test. If you leave the test and return later, you will need to log back in with the same email to resume.</li>
                    <li class="mb-4"><strong>No Pauses:</strong> There is no option to pause the test. Any interruptions, such as navigating away from the test page, will not stop the timer.</li>
                    <li class="mb-4"><strong>Webcam Monitoring:</strong> Please note that your webcam and audio may be monitored during the test to ensure no external assistance is being provided. Ensure your webcam is enabled, and no one else is present in your test-taking environment.</li>
                    <li><strong>One Attempt:</strong> You are allowed to take the test only once. Be prepared before you begin as there are no retakes allowed.</li>
                </ul>
                <p class="text-lg mt-16">By proceeding, you agree to these terms and conditions. <strong>Good luck!</strong></p>
                
                {{-- <a href="{{ route('test') }}" class="flex justify-end mt-10">
                    <button class="flex flex-row items-center right-0 gap-1 text-white bg-blue-700 hover:bg-blue-600 font-bold py-2 px-4 rounded text-center focus:outline-none focus:shadow-outline">
                        Start
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                </a> --}}
            </div>
        </div>
    </div>
</x-app-layout>