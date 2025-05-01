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
            // Client-side counter between polls
            document.addEventListener('DOMContentLoaded', function() {
                const timeDisplay = document.getElementById('time-display');
                const warningMessage = document.getElementById('warning-message');
                let timeLeft = parseInt(timeDisplay.dataset.timeLeft);
                let timerInterval;
                
                function updateTimerDisplay() {
                    const minutes = Math.floor(timeLeft / 60);
                    const seconds = timeLeft % 60;
                    timeDisplay.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                    
                    // Show warning when less than a minute remains
                    if (timeLeft <= 60) {
                        timeDisplay.classList.add('text-red-600', 'animate-pulse');
                        warningMessage.classList.remove('hidden');
                    } else {
                        timeDisplay.classList.remove('text-red-600', 'animate-pulse');
                        warningMessage.classList.add('hidden');
                    }
                    
                    // Handle timer completion
                    if (timeLeft <= 0) {
                        clearInterval(timerInterval);
                        const form = document.getElementById('questionForm');
                        if (form) {
                            const timeUpInput = document.createElement('input');
                            timeUpInput.type = 'hidden';
                            timeUpInput.name = 'time_up';
                            timeUpInput.value = '1';
                            form.appendChild(timeUpInput);
                            form.submit();
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
                
                // Start the countdown
                startClientCountdown();
                
                // Cleanup interval when component is destroyed
                document.addEventListener('beforeunload', () => {
                    clearInterval(timerInterval);
                });
                
                // Reset the counter when Livewire updates the component
                document.addEventListener('livewire:initialized', () => {
                    Livewire.on('timerUpdated', () => {
                        timeLeft = parseInt(timeDisplay.dataset.timeLeft);
                        updateTimerDisplay();
                        startClientCountdown();
                    });
                });
            });
        </script>
    <?php else: ?>
        <div class="text-base md:text-xl font-bold">
            Test not started
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div><?php /**PATH C:\Users\HeliaHaghighi\Desktop\MileleSkillSage\resources\views/livewire/test-timer.blade.php ENDPATH**/ ?>