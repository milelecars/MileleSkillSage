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
            <livewire:test-timer  :testId="$test->id" wire:loading.delay />
        </div>
            
        <!-- Main Content -->
        <livewire:test-player :test="$test" :candidate="$candidate" :questions="$questions" :currentIndex="$currentQuestionIndex" />

       
    </div>

    @push('scripts')
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        document.getElementById('questionForm').addEventListener('submit', function(e) {
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;
        });

        document.addEventListener('livewire:load', function () {
            Livewire.on('preserveStream', () => {
                window.__PRESERVE_STREAM__ = true;
            });
        });
    </script>
    @endpush
</x-app-layout>
