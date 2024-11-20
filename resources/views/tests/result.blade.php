<x-app-layout>
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
                            <div class="mt-8">
                            <a href="{{ route('reports.candidate-report', ['candidateId' => $candidate->id, 'testId' => $test->id]) }}" class="bg-purple-500 hover:bg-purple-600 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out">
                                Report
                            </a>

                            </div>
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
                        <a href="{{ route('candidate.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold  text-white hover:bg-blue-500 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Return to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>