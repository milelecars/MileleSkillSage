<div>
    @if($testStarted)
        <div class="flex flex-col items-center text-base md:text-xl font-bold" wire:poll.1s>
            <div>
                Remaining Time:
                <p class="inline {{ $timeLeft <= 60 ? 'text-red-600 animate-pulse' : '' }}">
                    {{ sprintf('%02d:%02d', $minutes, $seconds) }}
                </p>
                
            </div>
            
            @if($timeLeft <= 60)
                <p class="text-sm text-red-600 mt-1">Warning: Less than a minute remaining!</p>
            @endif
        </div>
    @else
        <div class="text-base md:text-xl font-bold">
            Test not started
        </div>
    @endif
</div>