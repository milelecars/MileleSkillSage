<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col items-center">
        <div class="bg-white rounded-lg shadow-md overflow-hidden w-full">
            <div class="flex flex-col md:flex-row">
                <!-- Question Section -->
                <div class="w-full md:w-[60%] p-4 sm:p-6 border-b md:border-b-0 md:border-r">
                    <div class="mb-4 text-xs md:text-sm text-gray-600">
                        Question {{ $currentIndex + 1 }} of {{ count($questions) }}
                    </div>
                    <h2 class="text-base md:text-xl font-medium mb-6">
                        {{ $question->question_text }}
                    </h2>

                    @if($question->question_type === 'MCQ')
                        @if($question->media instanceof \Illuminate\Database\Eloquent\Collection)
                            @foreach($question->media as $media)
                                @if($media->image_url)
                                    <img src="{{ $media->image_url }}"
                                        alt="{{ $media->description ?? 'Question Image' }}"
                                        class="mb-6 max-w-full rounded-lg border border-black">
                                @endif
                            @endforeach
                        @elseif(isset($question->media->image_url))
                            <img src="{{ $question->media->image_url }}"
                                alt="{{ $question->media->description ?? 'Question Image' }}"
                                class="mb-6 max-w-full rounded-lg border border-black">
                        @endif
                    @endif
                </div>

                <!-- Answer Section -->
                <div class="w-full md:w-[40%] p-6 bg-gray-50">
                    @if($question->question_type === 'MCQ')
                        <div class="space-y-4">
                            @foreach($question->choices as $choice)
                                <label class="flex items-start p-3 rounded-lg border border-gray-200 hover:bg-gray-100 cursor-pointer">
                                    <input type="radio"
                                        wire:model="selectedAnswer"
                                        value="{{ $choice->id }}"
                                        class="mt-1 form-radio text-blue-600"
                                        required>
                                    <span class="ml-3">
                                        <span class="font-medium text-sm md:text-base">{{ chr(65 + $loop->index) }}.</span>
                                        {{ $choice->choice_text }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @elseif($question->question_type === 'LSQ')
                        <div class="space-y-4 flex flex-col items-center justify-center">
                            <input type="range"
                                wire:model="lsqValue"
                                min="1"
                                max="5"
                                step="1"
                                class="w-[90%] cursor-pointer"
                                required>
                            <div class="flex justify-between text-sm text-theme font-bold mt-1 px-6 w-full">
                                <div class="w-12 text-[13px] md:text-base text-center -ml-6">Strongly Disagree</div>
                                <div class="w-16 text-[13px] md:text-base text-center -ml-1">Disagree</div>
                                <div class="w-14 text-[13px] md:text-base text-center -ml-1">Neutral</div>
                                <div class="w-12 text-[13px] md:text-base text-center mr-1">Agree</div>
                                <div class="w-12 text-[13px] md:text-base text-center -mr-6">Strongly Agree</div>
                            </div>
                        </div>
                    @endif

                    <!-- Submit / Next Button -->
                    <div class="mt-16 md:mt-6">
                        <button onclick="window.__PRESERVE_STREAM__ = true;" 
                            wire:click="submitAndNext"
                            class="w-full text-sm md:text-base text-white py-3 px-6 rounded-lg
                            {{ $currentIndex === count($questions) - 1 ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700' }}">
                            {{ $currentIndex === count($questions) - 1 ? 'Submit Test' : 'Next Question' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="h-1.5 bg-blue-100 w-[25%] mt-5">
            <div class="h-full bg-blue-600 rounded-full" style="width: {{ ($currentIndex + 1) / count($questions) * 100 }}%"></div>
        </div>

        <!-- Violation Log -->
        <div id="violation-log" class="fixed bottom-4 right-4 p-2 bg-black text-white text-xs rounded-lg opacity-50"></div>
    </div>
</div>
