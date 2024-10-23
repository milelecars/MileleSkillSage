<div>
    @if($testStarted)

        <div class="text-xl font-bold" wire:poll.1s>
            Remaining Time: 
            <p class="text-red-600 inline">{{ sprintf('%02d:%02d', $minutes, $seconds) }}</p>

        </div>
    @else
        <div class="text-xl font-bold">
            Test not started
        </div>
    @endif
</div>