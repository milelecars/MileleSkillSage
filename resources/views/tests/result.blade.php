<x-app-layout>
    <div 
        x-data="{ show: @if(session('warning')) true @else false @endif }"
        x-show="show"
        x-init="setTimeout(() => { show = false }, 5000)"
        class="fixed top-4 right-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg shadow-lg"
        style="z-index: 50;"
    >
        <div class="flex">
            <div class="py-1"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg></div>
            <div class="ml-3">
                <p class="font-medium">{{ session('warning') }}</p>
            </div>
            <div class="pl-3">
                <button @click="show = false" class="text-yellow-700 hover:text-yellow-900">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    <div class="py-12 text-theme bg-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="p-6 sm:p-10">
                    <h1 class="text-3xl font-bold text-gray-900 mb-6">Test Results</h1>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-20 rounded-lg">
                        <p class="text-blue-700">Thank you for completing the test!</p>
                    </div>


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h2 class="text-xl font-semibold mb-3">Test Information</h2>
                            <ul class="space-y-2">
                                <li><strong>Test Name:</strong> {{ $test->title }}</li>
                                <li><strong>Started At:</strong>
                                    @if($testAttempt->pivot->started_at)
                                        {{ \Carbon\Carbon::parse($testAttempt->pivot->started_at)->format('M d, Y H:i:s') }}
                                    @else
                                        N/A
                                    @endif
                                </li>
                                <li><strong>Completed At:</strong>
                                    @if($testAttempt->pivot->completed_at)
                                        @php
                                            $startedAt = \Carbon\Carbon::parse($testAttempt->pivot->started_at);
                                            $completedAt = \Carbon\Carbon::parse($testAttempt->pivot->completed_at);
                                            $expectedEndTime = $startedAt->copy()->addMinutes($test->duration);
                                        @endphp
                                        @if($completedAt->gt($expectedEndTime))
                                            {{ $expectedEndTime->format('M d, Y H:i:s') }}
                                        @else
                                            {{ $completedAt->format('M d, Y H:i:s') }}
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </li>

                                <li>
                                    <strong>Duration:</strong>
                                    @if($testAttempt->pivot->started_at && $testAttempt->pivot->completed_at)
                                        @php
                                            $startedAt = \Carbon\Carbon::parse($testAttempt->pivot->started_at);
                                            $completedAt = \Carbon\Carbon::parse($testAttempt->pivot->completed_at);
                                            $duration = $startedAt->diff($completedAt);
                                            $durationInMinutes = $duration->days * 24 * 60 + $duration->h * 60 + $duration->i;
                                            $durationInSeconds = $duration->s;
                                        @endphp
                                        {{ $durationInMinutes }} {{ Str::plural('minute', $durationInMinutes) }} and {{ $durationInSeconds }} {{ Str::plural('second', $durationInSeconds) }}
                                    @else
                                        N/A
                                    @endif
                                </li>

                            </ul>
                        </div>
                        
                        <div>
                            <h2 class="text-xl font-semibold mb-3">Score Summary</h2>
                            <div class="bg-gray-100 p-4 rounded-lg">
                                <div class="text-4xl font-bold text-center text-blue-600">
                                    {{ $testAttempt->pivot->score ?? 0 }} / {{ count($questions ?? []) }}
                                </div>
                                <p class="text-center text-gray-600 mt-2">Correct Answers</p>
                            </div>
                            <div class="mt-4">
                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ ($candidate->test_score / count($questions) * 100) }}%"></div>
                                </div>
                                <p class="text-center text-gray-600 mt-2">
                                {{ round(($testAttempt->pivot->score / count($questions)) * 100, 1) }}% Score
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10">
                        <h2 class="text-xl font-semibold mb-3">🌟Performance Feedback🌟</h2>
                        <p class="text-gray-700">
                            @if(($testAttempt->pivot->score/ count($questions)) >= 0.8)
                                Excellent work! Your high score demonstrates a strong understanding of the subject matter.
                            @elseif(($testAttempt->pivot->score/ count($questions)) >= 0.6)
                                Good job! You've shown a solid grasp of many key concepts, but there's still room for improvement.
                            @else
                                Thank you for completing the test. We recommend further study to improve your understanding of the material.
                            @endif
                        </p>
                    </div>

                    <div class="mt-20 flex justify-end w-full">
                        <a href="{{ route('candidate.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold  text-white hover:bg-blue-500 disabled:opacity-25 transition ease-in-out duration-150">
                            Return to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>