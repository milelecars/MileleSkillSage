<x-app-layout>
    <div class="min-h-screen bg-gray-100">
        <input type="hidden" id="test-id" value="{{ $test->id }}">
        <input type="hidden" id="candidate-id" value="{{ $candidate->id }}">
        
        {{-- Camera Section --}}
        <div class="rounded-lg overflow-hidden bg-gray-50 p-4 hidden">
            <video id="video" class="w-full h-auto rounded-lg shadow-inner border-2 border-gray-200" autoplay playsinline></video>
            <div id="detection-status" class="mt-3 text-sm text-gray-600"></div>
        </div>

        <!-- Fixed Timer Bar -->
        <div class="w-full flex flex-col gap-3 items-center justify-center my-8">
            <livewire:test-timer :testId="$test->id" />
        </div>
            
        <!-- Main Content -->
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col items-center">
                <div class="bg-white rounded-lg shadow-md overflow-hidden w-full">
                    <div class="md:flex">
                        <!-- Question Section -->
                        <div class="md:w-2/3 p-6 border-r">
                            <div class="mb-4 text-sm text-gray-600">
                                Question {{ $currentQuestionIndex + 1 }} of {{ $questions->count() }}
                            </div>
                            <h2 class="text-xl font-medium mb-6">
                                {{ $questions[$currentQuestionIndex]->question_text }}
                            </h2>

                            {{-- Question Media --}}
                            @if($questions[$currentQuestionIndex]->media && $questions[$currentQuestionIndex]->media instanceof \Illuminate\Database\Eloquent\Collection)
                                @foreach($questions[$currentQuestionIndex]->media as $media)
                                    @if($media->image_url)
                                        <img src="{{ $media->image_url }}" 
                                            alt="{{ $media->description ?? 'Question Image' }}" 
                                            class="mb-6 max-w-full rounded-lg">
                                    @endif
                                @endforeach
                            @elseif($questions[$currentQuestionIndex]->media && isset($questions[$currentQuestionIndex]->media->image_url))
                                <img src="{{ $questions[$currentQuestionIndex]->media->image_url }}" 
                                    alt="{{ $questions[$currentQuestionIndex]->media->description ?? 'Question Image' }}" 
                                    class="mb-6 max-w-full rounded-lg">
                            @endif
                        </div>

                        <!-- Choices -->
                        <div class="md:w-1/3 p-6 bg-gray-50">
                            <form id="questionForm" method="POST" 
                                  action="{{ $currentQuestionIndex === $questions->count() - 1 
                                    ? route('tests.submit', ['id' => $test->id]) 
                                    : route('tests.next', ['id' => $test->id]) }}">
                                @csrf
                                <input type="hidden" name="current_index" value="{{ $currentQuestionIndex }}">

                                {{-- Choices --}}
                                <div class="space-y-4">
                                    @foreach($questions[$currentQuestionIndex]->choices as $choice)
                                        <label class="flex items-start p-3 rounded-lg border border-gray-200 hover:bg-gray-100 cursor-pointer">
                                            <input type="radio" 
                                                name="answer" 
                                                value="{{ $choice->id }}" 
                                                class="mt-1 form-radio text-blue-600" 
                                                {{ session()->get("test_session.answers.$currentQuestionIndex") === $choice->id ? 'checked' : '' }}
                                                required>
                                            <span class="ml-3">
                                                <span class="font-medium">{{ chr(65 + $loop->index) }}.</span>
                                                {{ $choice->choice_text }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>

                                {{-- Submit Button --}}
                                <div class="mt-6">
                                    <button type="submit" 
                                            class="w-full text-white py-3 px-6 rounded-lg 
                                            {{ $currentQuestionIndex === $questions->count() - 1 ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700' }}">
                                        {{ $currentQuestionIndex === $questions->count() - 1 ? 'Submit Test' : 'Next Question' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="h-1.5 bg-blue-100 w-[25%] mt-5">
                    <div class="h-full bg-blue-600 rounded-full" style="width: {{ ($currentQuestionIndex + 1) / count($questions) * 100 }}%"></div>
                </div>

                <!-- Violation Log -->
                <div id="violation-log" class="fixed bottom-4 right-4 p-2 bg-black text-white text-xs rounded-lg opacity-50"></div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // Add form submission handling
        document.getElementById('questionForm').addEventListener('submit', function(e) {
            // Disable submit button to prevent double submission
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;
        });
    </script>
    @endpush
</x-app-layout>