<div>
    @if($testStarted)
        <div class="text-xl font-bold text-red-600" wire:poll.1s>
            Remaining Time: {{ sprintf('%02d:%02d', $minutes, $seconds) }}
        </div>
    @else
        <div class="text-xl font-bold">
            Test not started
        </div>
    @endif
</div>