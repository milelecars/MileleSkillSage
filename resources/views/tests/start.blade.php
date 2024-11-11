<x-app-layout>
    <style>
        .prevent-select {
            -webkit-user-select: none; /* Safari */
            -ms-user-select: none; /* IE 10 and IE 11 */
            user-select: none; /* Standard syntax */
        }
    </style>
    
    <div class="min-h-screen bg-gray-100">
        {{-- Camera Section --}}
        <div class="rounded-lg overflow-hidden bg-gray-50 p-4 hidden">
            <video id="video" class="w-full h-auto  rounded-lg shadow-inner border-2 border-gray-200" autoplay playsinline></video>
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
                                Question {{ $currentQuestionIndex + 1 }} of {{ count($questions) }}
                            </div>
                            <h2 class="text-xl font-medium mb-6">
                                {{ $questions[$currentQuestionIndex]['question'] }}
                            </h2>
                            @if(isset($questions[$currentQuestionIndex]['image_url']))
                                <img src="{{ $questions[$currentQuestionIndex]['image_url'] }}" alt="Question Image" class="mb-6 max-w-full rounded-lg">
                            @endif
                        </div>

                        <!-- Options Section -->
                        <div class="md:w-1/3 p-6 bg-gray-50">
                            <form method="POST" action="{{ route('tests.next', ['id' => $test->id]) }}">
                                @csrf
                                <input type="hidden" name="current_index" value="{{ $currentQuestionIndex }}">
                                <div class="space-y-4">
                                    @foreach(['a', 'b', 'c', 'd'] as $choice)
                                        @if(isset($questions[$currentQuestionIndex]['choice_'.$choice]))
                                            <label class="flex items-start p-3 rounded-lg border border-gray-200 hover:bg-gray-100 cursor-pointer">
                                                <input type="radio" name="answer" value="{{ $choice }}" class="mt-1 form-radio text-blue-600" 
                                                    {{ session()->get("test_session.answers.$currentQuestionIndex") === $choice ? 'checked' : '' }} 
                                                    required>
                                                <span class="ml-3">
                                                    <span class="font-medium">{{ strtoupper($choice) }}.</span>
                                                    {{ $questions[$currentQuestionIndex]['choice_'.$choice] }}
                                                </span>
                                            </label>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="mt-6">
                                    <button type="submit" 
                                        class="w-full text-white py-3 px-6 rounded-lg 
                                        {{ $currentQuestionIndex === count($questions) - 1 ? 'bg-red-600 hover:bg-red-700 ' : 'bg-blue-600 hover:bg-blue-700 ' }}">

                                        {{ $currentQuestionIndex === count($questions) - 1 ? 'Submit Test' : 'Next Question' }}
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

                <!-- Test Monitoring Results  -->
                <div class="mt-4 p-4 bg-red-200 rounded-lg shadow monitoring-summary">
                    <h3 class="text-lg font-semibold">Test Monitoring Summary</h3>
                    <div class="mt-2 grid grid-cols-2 gap-4">
                        <div>
                            <p class="font-medium">
                                Tab Switches: 
                                <span data-metric="tabSwitches" class="text-gray-600">0</span>
                                <br/>
                                <small>Flagged: <span data-metric-flag="tabSwitches" class="text-green-600">No</span></small>
                            </p>
                            <p class="font-medium">
                                Window Blurs: 
                                <span data-metric="windowBlurs" class="text-gray-600">0</span>
                                <br/>
                                <small>Flagged: <span data-metric-flag="windowBlurs" class="text-green-600">No</span></small>
                            </p>
                            <p class="font-medium">
                                Mouse Exits: 
                                <span data-metric="mouseExits" class="text-gray-600">0</span>
                                <br/>
                                <small>Flagged: <span data-metric-flag="mouseExits" class="text-green-600">No</span></small>
                            </p>
                            <p class="font-medium">
                                Copy/Cut Attempts: 
                                <span data-metric="copyCutAttempts" class="text-gray-600">0</span>
                                <br/>
                                <small>Flagged: <span data-metric-flag="copyCutAttempts" class="text-green-600">No</span></small>
                            </p>
                        </div>
                        <div>
                            <p class="font-medium">
                                Right Clicks: 
                                <span data-metric="rightClicks" class="text-gray-600">0</span>
                                <br/>
                                <small>Flagged: <span data-metric-flag="rightClicks" class="text-green-600">No</span></small>
                            </p>
                            <p class="font-medium">
                                Keyboard Shortcuts: 
                                <span data-metric="keyboardShortcuts" class="text-gray-600">0</span>
                                <br/>
                                <small>Flagged: <span data-metric-flag="keyboardShortcuts" class="text-green-600">No</span></small>
                            </p>
                            <p class="font-medium">
                                Total Warnings: 
                                <span data-metric="warningCount" class="text-gray-600">0</span>
                                <br/>
                                <small>Flagged: <span data-metric-flag="warningCount" class="text-green-600">No</span></small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/webcam.js') }}"></script>
    <script src="{{ asset('js/test-monitoring.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            disableCopyPaste();
            // disableRightClick();
            disableKeyboardShortcuts();
        });
    
        function disableCopyPaste() {
            document.addEventListener('copy', function(e) {
                e.preventDefault();
            });
    
            document.addEventListener('cut', function(e) {
                e.preventDefault();
            });
    
            document.addEventListener('paste', function(e) {
                e.preventDefault();
            });
        }

        // function disableRightClick() {
        //     document.addEventListener('contextmenu', function(e) {
        //         e.preventDefault();
        //     });
        // }

        function disableKeyboardShortcuts() {
            document.addEventListener('keydown', function(e) {
                // Prevent Ctrl+C, Ctrl+V, Ctrl+X
                if ((e.ctrlKey || e.metaKey) && (e.key === 'c' || e.key === 'v' || e.key === 'x')) {
                    e.preventDefault();
                }
                
                // Prevent F12 key (Developer Tools)
                if (e.key === 'F12') {
                    e.preventDefault();
                }

                // Prevent Ctrl+Shift+I (Developer Tools)
                if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'i') {
                    e.preventDefault();
                }

                // Prevent Ctrl+Shift+C (Developer Tools Element Inspector)
                if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'c') {
                    e.preventDefault();
                }

                // Prevent Alt+Text Selection
                if (e.altKey) {
                    e.preventDefault();
                }
            });
        }
    </script>
</x-app-layout>