<div>
    @if($testStarted)
        <div class="text-xl font-bold" wire:poll.1s>
            {{ sprintf('%02d:%02d', $minutes, $seconds) }}
        </div>
    @else
        <div class="text-xl font-bold">
            Test not started
        </div>
    @endif
</div>