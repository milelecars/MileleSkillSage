<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Header -->
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Test Results</h1>
                        <p class="text-gray-600">{{ $candidate->name }} ({{ $candidate->email }})</p>
                    </div>

                    <!-- Test Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h2 class="font-semibold text-lg mb-2">Test Details</h2>
                            <ul class="space-y-2">
                                <li><span class="font-medium">Test Name:</span> {{ $test->name }}</li>
                                <li><span class="font-medium">Started:</span> {{ $candidate->test_started_at->format('M d, Y H:i') }}</li>
                                <li><span class="font-medium">Completed:</span> {{ $candidate->test_completed_at->format('M d, Y H:i') }}</li>
                                <li><span class="font-medium">Duration:</span> 
                                    {{ $candidate->test_started_at->diff($candidate->test_completed_at)->format('%H:%I:%S') }}
                                </li>
                            </ul>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <h2 class="font-semibold text-lg mb-2">Performance Summary</h2>
                            <ul class="space-y-4">
                                <li>
                                    <span class="font-medium">Score:</span> 
                                    <span class="text-gray-900">{{ $candidate->test_score }} / {{ $totalQuestions }} points</span>
                                </li>
                                
                                <li>
                                    <div class="mt-1">
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full" 
                                                 style="width: {{ $percentage }}%">
                                            </div>
                                        </div>
                                        <div class="text-sm text-gray-600 mt-1">
                                            {{ number_format($percentage, 1) }}% Correct
                                        </div>
                                    </div>
                                </li>

                                <li>
                                    <span class="font-medium">Status:</span>
                                    @if($testAttempt->is_expired)
                                        <span class="text-yellow-600">Submitted (Time Expired)</span>
                                    @else
                                        <span class="text-green-600">Completed</span>
                                    @endif
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex justify-end space-x-4">
                        <a href="{{ route('manage-candidates') }}" 
                           class="bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200">
                            Back to Candidates
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>