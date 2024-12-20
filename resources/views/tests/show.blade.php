<x-app-layout>
    <div class="py-12 text-theme bg-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="p-8">
                    {{-- Camera Section --}}
                    @if(Auth::guard('candidate')->check())
                        <div class="rounded-lg overflow-hidden bg-gray-50 p-4 hidden">
                            <video id="video" class="w-full h-auto rounded-lg shadow-inner border-2 border-gray-200" autoplay playsinline></video>
                            <div id="detection-status" class="mt-3 text-sm text-gray-600"></div>
                        </div>
                    @endif

                    <div class="flex justify-between items-center mb-4">
                        <div class="flex flex-col justify-between">
                            <h1 class="text-2xl font-extrabold text-gray-900">{{$test->title}}</h1>
                            
                        </div>  
                        <a href="{{ route('tests.invite', $test->id) }}" class="inline-flex items-center bg-green-500 hover:bg-green-600 text-white font-bold px-4 py-2 rounded-md text-sm transition duration-300 ease-in-out">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="19" height="19" class="mr-2">
                                <path fill="#ffffff" d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                            </svg>    
                            Invite
                        </a>
                    </div>
                    <div class="flex items-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mr-1" viewBox="0 0 24 24" width="18" height="18">
                            <path fill="#666666" d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.4 0-8-3.6-8-8s3.6-8 8-8 8 3.6 8 8-3.6 8-8 8zm.5-13H11v6l5.2 3.2.8-1.3-4.5-2.7V7z"/>
                        </svg>
                        Duration: {{$test->duration}}
                    </div>
                    <p class="text-lg mb-8 text-gray-700 leading-relaxed text-justify">
                        {{$test->description}}
                    </p>
                
                    {{-- actions for admin users --}}
                    @if(Auth::guard('web')->check())
                        <div class="flex justify-end space-x-4 my-6">
                            <a href="{{ route('tests.edit', $test->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold px-4 py-2 rounded-md text-sm transition duration-300 ease-in-out">
                                Edit
                            </a>
                            <form action="{{ route('tests.destroy', $test->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to archive this test? All existing data will be preserved.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold px-4 py-2 rounded-md text-sm transition duration-300 ease-in-out">
                                    Archive
                                </button>
                            </form>
                        </div>
                
                    
                        {{-- test preview for admin users --}}
                        @if($questions->count() > 0)
                            <div class="mt-8 space-y-8">
                                <h2 class="text-2xl font-bold text-gray-800 mb-4">Test Preview</h2>
                                @foreach ($questions->take(10) as $index => $question)
                                    <div class="bg-gray-50 p-6 rounded-lg shadow">
                                        <p class="text-lg mb-4 font-medium text-gray-800">
                                            {{$index + 1}}. {{ $question->question_text }}
                                        </p>

                                        @if($question->media && $question->media instanceof \Illuminate\Database\Eloquent\Collection)
                                            @foreach($question->media as $media)
                                                @if($media->image_url)
                                                    <img src="{{ $media->image_url }}" 
                                                        alt="{{ $media->description ?? 'Question Image' }}" 
                                                        class="mb-4 max-w-full h-auto rounded">
                                                @endif
                                            @endforeach
                                        @elseif($question->media && isset($question->media->image_url))
                                            <img src="{{ $question->media->image_url }}" 
                                                alt="{{ $question->media->description ?? 'Question Image' }}" 
                                                class="mb-4 max-w-full h-auto rounded">
                                        @endif
                                        
                                        <div class="space-y-2 ml-4 mb-4">
                                            @foreach($question->choices as $choice)
                                                <div class="text-gray-700">
                                                    {{ chr(65 + $loop->index) }}. {{ $choice->choice_text }}
                                                </div>
                                            @endforeach
                                        </div>
                                        
                                        @if(Auth::guard('web')->check())
                                            @php
                                                $correctChoice = $question->choices->firstWhere('is_correct', true);
                                                $correctIndex = $correctChoice ? $question->choices->search($correctChoice) : null;
                                            @endphp
                                            @if($correctIndex !== null)
                                                <p class="mt-4 font-semibold text-green-600">
                                                    Answer: {{ chr(65 + $correctIndex) }}
                                                </p>
                                            @endif
                                        @endif
                                    </div>
                                @endforeach

                                @if($questions->count() > 10)
                                    <p class="text-gray-600 italic mt-4">
                                        Showing 10 out of {{ $questions->count() }} questions...
                                    </p>
                                @endif
                            </div>
                        @else
                            <p class="text-gray-600 italic">No questions available for this test.</p>
                        @endif
                        
                    @endif
                
                    {{-- content for candidates --}}
                    @if(Auth::guard('candidate')->check())
                        @if($isTestCompleted)
                            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                                <p>You have already completed this test.</p>
                                <a href="{{ route('tests.result', ['id' => $test->id]) }}" class="font-bold underline">View Results</a>
                            </div>
                        @elseif($isInvitationExpired)
                            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                                <p>The invitation for this test has expired.</p>
                            </div>
                        @elseif($isTestStarted)
                            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6" role="alert">
                                <p>You have an ongoing test session.</p>
                                <a href="{{ route('tests.start', ['id' => $test->id]) }}" class="font-bold underline">Continue Test</a>
                            </div>
                        @else
                            <div class="bg-yellow-100 border-l-4 border-yellow-500 rounded-lg text-yellow-700 p-4 mb-6" role="alert">
                                <p>You have {{ $test->duration }} minutes to complete this test once you start.</p>
                            </div>
                            <div class="mt-8 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                                <div class="flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#AA2E26" class="w-6 h-6 mr-2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                    </svg>
                                    <h2 class="text-xl font-bold text-red-700">Important Guidelines</h2>
                                </div>
                                <ul class="text-base text-gray-700 list-disc pl-6 space-y-3">
                                    <li><strong>One Attempt:</strong> You can take the test only once. Be prepared before starting.</li>
                                    <li><strong>Test Duration:</strong> Once started, the timer continues even if you close the browser. Complete all questions in one session.</li>
                                    <li><strong>Allowed:</strong> You are free to use a calculator, pen and paper.</li>
                                    <li><strong>No Pauses:</strong> The test cannot be paused and any interruptions won't stop the timer.</li>
                                    <li><strong>Email Requirement:</strong> Use the same email throughout. You'll need it to resume if you leave or accidentally close the web page.</li>
                                    <li><strong>Webcam Monitoring:</strong> Your webcam and audio may be monitored. Ensure it's enabled and you're alone.</li>
                                </ul>
                            </div>
                            <form action="{{ route('tests.start', $test->id) }}" method="POST" class="mt-8">
                                @csrf
                                <div class="p-2 flex items-center space-x-2">
                                    <input type="checkbox" name="agreement" id="agreement" class="rounded border-black" required>
                                    <label for="agreement" class="text-sm text-gray-600">
                                        I agree to the <a href="#" class="text-blue-600 hover:underline">Terms of Service</a> and acknowledge that I have read the <a href="#" class="text-blue-600 hover:underline">Guidelines</a>
                                    </label>
                                </div>
                                <div class="flex justify-end mt-8">
                                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                        Start Test
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 ml-2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </button>
                                </div>
                            </form>
                            <!-- <div x-data="{ agreed: false }">
                                <div class="mt-5 p-2 flex items-center space-x-2">
                                    <input type="checkbox" 
                                        x-model="agreed" 
                                        id="agreement" 
                                        class="rounded border-black">
                                    <label for="agreement" class="text-sm text-gray-600">
                                        I agree to the <a href="#" class="text-blue-600 hover:underline">Terms of Service</a> and acknowledge that I have read the <a href="#" class="text-blue-600 hover:underline">Guidelines</a>
                                    </label>
                                </div>
                                <span x-show="!agreed" x-cloak class="text-red-500 text-sm">You must agree to the terms to continue</span>
                                
                                <div class="flex justify-end mt-8">
                                    <button @click="if(agreed) window.location.href='{{ route('tests.start', $test->id) }}'"
                                            :class="{ 'opacity-50 cursor-not-allowed': !agreed, 'hover:bg-blue-700': agreed }"
                                            class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-md">
                                        Start Test
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 ml-2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </button>
                                </div>
                            </div> -->
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>