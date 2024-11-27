<div>
    <!--[if BLOCK]><![endif]--><?php if($testStarted): ?>
        <div class="flex flex-col items-center text-xl font-bold" wire:poll.1s>
            <div>
                Remaining Time:
                <p class="inline <?php echo e($timeLeft <= 60 ? 'text-red-600 animate-pulse' : ''); ?>">
                    <?php echo e(sprintf('%02d:%02d', $minutes, $seconds)); ?>

                </p>
                
            </div>
            
            <!--[if BLOCK]><![endif]--><?php if($timeLeft <= 60): ?>
                <p class="text-sm text-red-600 mt-1">Warning: Less than a minute remaining!</p>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    <?php else: ?>
        <div class="text-xl font-bold">
            Test not started
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div><?php /**PATH C:\Users\HeliaHaghighi\Desktop\AGCT-Software\resources\views/livewire/test-timer.blade.php ENDPATH**/ ?>