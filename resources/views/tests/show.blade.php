<x-app-layout>
    <div class="py-12 text-theme bg-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="p-8">
                    <h1 class="text-2xl font-extrabold mb-4 text-gray-900">{{$test->name}}</h1>
                    <p class="text-lg mb-8 text-gray-700 leading-relaxed">
                        {{$test->description}}
                    </p>
                
                    {{-- actions for admin users --}}
                    @if(Auth::guard('web')->check())
                        <div class="flex justify-end space-x-4 my-6">
                            <a href="{{ route('tests.edit', $test->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out">
                                Edit
                            </a>
                            <form action="{{ route('tests.destroy', $test->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this test?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out">
                                    Delete
                                </button>
                            </form>
                            <a href="{{ route('tests.invite', $test->id) }}" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out">
                                Invite
                            </a>
                        </div>
                
                        {{-- test preview for admin users --}}
                        @if(!empty($questions))
                            <div class="mt-8 space-y-8">
                                <h2 class="text-2xl font-bold text-gray-800 mb-4">Test Preview</h2>
                                @foreach (array_slice($questions, 0, 10) as $question)
                                    <div class="bg-gray-50 p-6 rounded-lg shadow">
                                        <p class="text-lg mb-4 font-medium text-gray-800">
                                            {{ $question['question'] ?? 'No question available' }}
                                        </p>
                                        
                                        @if(isset($question['image_url']) && $question['image_url'])
                                            <img src="{{ $question['image_url'] }}" alt="Question Image" class="mb-4 max-w-full h-auto rounded">
                                        @endif
                                        
                                        <div class="space-y-2 ml-4 mb-4">
                                            @foreach(['a', 'b', 'c', 'd'] as $choice)
                                                @if(isset($question['choice_'.$choice]))
                                                    <label class="flex items-center space-x-3">
                                                        <input type="radio" name="option_{{ $loop->parent->index }}" value="{{ $question['choice_'.$choice] }}" class="form-radio text-blue-600">
                                                        <span class="text-gray-700">{{ ucfirst($choice) }}. {{ $question['choice_'.$choice] }}</span>
                                                    </label>
                                                @endif
                                            @endforeach
                                        </div>
                                        
                                        @if(isset($question['answer']))
                                            <p class="mt-4 font-semibold text-green-600">Answer: {{ $question['answer'] }}</p>
                                        @endif
                                    </div>
                                @endforeach
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
                                    <li><strong>Test Duration:</strong> Once started, the timer continues even if you close the browser. Complete all questions in one session.</li>
                                    <li><strong>Email Requirement:</strong> Use the same email throughout. You'll need it to resume if you leave.</li>
                                    <li><strong>No Pauses:</strong> The test cannot be paused. Interruptions won't stop the timer.</li>
                                    <li><strong>Webcam Monitoring:</strong> Your webcam and audio may be monitored. Ensure it's enabled and you're alone.</li>
                                    <li><strong>One Attempt:</strong> You can take the test only once. Be prepared before starting.</li>
                                </ul>
                            </div>
                            <div class="flex justify-end mt-8">
                                <a href="{{ route('tests.start', $test->id) }}" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-md">
                                    Start Test
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 ml-2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                </a>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>