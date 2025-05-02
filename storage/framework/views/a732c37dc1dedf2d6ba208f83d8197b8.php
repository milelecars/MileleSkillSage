<div>
    <!--[if BLOCK]><![endif]--><?php if($testStarted): ?>
        <div class="flex flex-col items-center text-base md:text-xl font-bold">
            <div>
                Remaining Time:
                <p class="inline <?php echo e($timeLeft <= 60 ? 'text-red-600 animate-pulse' : ''); ?>" 
                   id="time-display" 
                   data-time-left="<?php echo e($timeLeft); ?>">
                    <?php echo e(sprintf('%02d:%02d', $minutes, $seconds)); ?>

                </p>
            </div>
            
            <p class="text-sm text-red-600 mt-1 <?php echo e($timeLeft <= 60 ? '' : 'hidden'); ?>" id="warning-message">
                Warning: Less than a minute remaining!
            </p>
        </div>
        
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const timeDisplay = document.getElementById('time-display');
                const warningMessage = document.getElementById('warning-message');
                let timeLeft = parseInt(timeDisplay.dataset.timeLeft);
                let timerInterval;

                function updateTimerDisplay() {
                    const minutes = Math.floor(timeLeft / 60);
                    const seconds = timeLeft % 60;
                    timeDisplay.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

                    if (timeLeft <= 60) {
                        timeDisplay.classList.add('text-red-600', 'animate-pulse');
                        warningMessage.classList.remove('hidden');
                    } else {
                        timeDisplay.classList.remove('text-red-600', 'animate-pulse');
                        warningMessage.classList.add('hidden');
                    }

                    if (timeLeft <= 0) {
                        clearInterval(timerInterval);
                        console.log("⏰ Timer hit 0 — trying to emit Livewire event");

                        if (typeof Livewire !== 'undefined') {
                            window.Livewire.dispatch('timeExpired');
                            console.log("📡 Livewire.emit('timeExpired') called");
                        } else {
                            console.error("❌ Livewire not initialized!");
                        }
                    }
                }

                function startClientCountdown() {
                    clearInterval(timerInterval);
                    timerInterval = setInterval(() => {
                        timeLeft--;
                        updateTimerDisplay();
                    }, 1000);
                }

                startClientCountdown();
            });

        </script>
    <?php else: ?>
        <div class="text-base md:text-xl font-bold">
            Test not started
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div><?php /**PATH C:\Users\HeliaHaghighi\Desktop\MileleSkillSage\resources\views/livewire/test-timer.blade.php ENDPATH**/ ?>