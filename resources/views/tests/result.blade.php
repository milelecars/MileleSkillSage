<x-app-layout>
    <div class="py-12 text-theme bg-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="p-6 sm:p-10">
                    <h1 class="text-3xl font-bold text-gray-900 mb-6">Test Results</h1>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                        <p class="text-blue-700">Thank you for completing the test!</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h2 class="text-xl font-semibold mb-3">Test Information</h2>
                            <ul class="space-y-2">
                                {{-- <li><strong>Test Name:</strong> {{ $candidate->test_name }}</li>
                                <li><strong>Started At:</strong> {{ $candidate->test_started_at->format('M d, Y H:i:s') }}</li>
                                <li><strong>Completed At:</strong> {{ $candidate->test_completed_at->format('M d, Y H:i:s') }}</li>
                                <li><strong>Duration:</strong> 
                                    {{ $candidate->test_started_at->diffInMinutes($candidate->test_completed_at) }} minutes
                                </li> --}}
                            </ul>
                        </div>
                        
                        <div>
                            <h2 class="text-xl font-semibold mb-3">Score Summary</h2>
                            <div class="bg-gray-100 p-4 rounded-lg">
                                <div class="text-4xl font-bold text-center text-blue-600">
                                    {{-- {{ $candidate->test_score }} / {{ $test->questions->count() }} --}}
                                </div>
                                <p class="text-center text-gray-600 mt-2">Correct Answers</p>
                            </div>
                            <div class="mt-4">
                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    {{-- <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ ($candidate->test_score / $test->questions->count()) * 100 }}%"></div> --}}
                                </div>
                                <p class="text-center text-gray-600 mt-2">
                                    {{-- {{ number_format(($candidate->test_score / $test->questions->count()) * 100, 1) }}% Score --}}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10">
                        <h2 class="text-xl font-semibold mb-3">Performance Feedback</h2>
                        <p class="text-gray-700">
                            {{-- @if(($candidate->test_score / $test->questions->count()) >= 0.8)
                                Excellent work! Your high score demonstrates a strong understanding of the subject matter.
                            @elseif(($candidate->test_score / $test->questions->count()) >= 0.6)
                                Good job! You've shown a solid grasp of many key concepts, but there's still room for improvement.
                            @else
                                Thank you for completing the test. We recommend further study to improve your understanding of the material.
                            @endif --}}
                        </p>
                    </div>

                    <div class="mt-10">
                        <a href="{{ route('candidate.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Return to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>