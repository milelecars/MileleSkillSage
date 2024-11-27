<div 
    x-data="{ 
        initMonitoring() {
            document.addEventListener('webcamViolation', (event) => {
                window.Livewire.find('<?php echo e($_instance->getId()); ?>').logSuspiciousBehavior(event.detail.violation);
                console.log('⚠️ Webcam violation detected:', event.detail.violation);
            });

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    window.Livewire.find('<?php echo e($_instance->getId()); ?>').logSuspiciousBehavior('Tab Switches');
                    console.log('⚠️ Tab Switching Detected!');
                }
            });

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    window.Livewire.find('<?php echo e($_instance->getId()); ?>').logSuspiciousBehavior('Tab Switches');
                    console.log('⚠️ Tab Switching Detected!');
                }
            });

            window.addEventListener('blur', () => {
                window.Livewire.find('<?php echo e($_instance->getId()); ?>').logSuspiciousBehavior('Window Blurs');
                console.log('⚠️ Window focus lost!');
            });

            document.addEventListener('mouseleave', () => {
                window.Livewire.find('<?php echo e($_instance->getId()); ?>').logSuspiciousBehavior('Mouse Exits');
                console.log('⚠️ Mouse exit detected!');
            });

            document.addEventListener('copy', (e) => {
                e.preventDefault();
                window.Livewire.find('<?php echo e($_instance->getId()); ?>').logSuspiciousBehavior('Copy/Cut Attempts');
                console.log('⚠️ Copying is not allowed!');
            });

            document.addEventListener('cut', (e) => {
                e.preventDefault();
                window.Livewire.find('<?php echo e($_instance->getId()); ?>').logSuspiciousBehavior('Copy/Cut Attempts');
                console.log('⚠️ Cutting is not allowed!');
            });

            document.addEventListener('contextmenu', (e) => {
                e.preventDefault();
                window.Livewire.find('<?php echo e($_instance->getId()); ?>').logSuspiciousBehavior('Right Clicks');
                console.log('⚠️ Right clicking is not allowed!');
            });

            document.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && 
                    ['c', 'v', 'x', 'a', 'p', 'p'].includes(e.key.toLowerCase())) {
                    e.preventDefault();
                    window.Livewire.find('<?php echo e($_instance->getId()); ?>').logSuspiciousBehavior('Keyboard Shortcuts');
                    console.log('⚠️ Keyboard shortcut detected!', e.key);
                }
            });
        }
    }"
    x-init="initMonitoring()"
    class="mt-4 p-4 bg-red-200 rounded-lg shadow monitoring-summary"
>
    <h3 class="text-lg font-semibold">Test Monitoring Summary</h3>
    <div class="mt-2 grid grid-cols-2 gap-4">
        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $flags->chunk(ceil($flags->count() / 2)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chunk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div>
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $chunk; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $flagType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $metricKey = lcfirst(str_replace(' ', '', $flagType->name));
                    ?>
                    <p class="font-medium">
                        <?php echo e($flagType->name); ?>: 
                        <span class="<?php echo e($metrics[$metricKey] > $flagType->threshold ? 'text-red-600' : 'text-gray-600'); ?>">
                            <?php echo e($metrics[$metricKey]); ?>

                        </span>
                        <br/>
                        <small>
                            Flagged: 
                            <span class="<?php echo e($metrics[$metricKey] > $flagType->threshold ? 'text-red-600' : 'text-green-600'); ?>">
                                <?php echo e($metrics[$metricKey] > $flagType->threshold ? 'Yes' : 'No'); ?>

                            </span>
                            <span class="text-xs text-gray-500">(Threshold: <?php echo e($flagType->threshold); ?>)</span>
                        </small>
                    </p>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
    </div>
</div><?php /**PATH C:\Users\HeliaHaghighi\Desktop\AGCT-Software\resources\views/livewire/test-monitoring.blade.php ENDPATH**/ ?>