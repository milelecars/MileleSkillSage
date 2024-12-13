<div class="mt-4 p-4 bg-red-200 rounded-lg shadow monitoring-summary">
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
                        <span data-metric="<?php echo e($metricKey); ?>" class="text-gray-600">0</span>
                        <br/>
                        <small>
                            Flagged: 
                            <span data-metric-flag="<?php echo e($metricKey); ?>" class="text-green-600">No</span>
                            <span class="text-xs text-gray-500">(Threshold: <?php echo e($flagType->threshold); ?>)</span>
                        </small>
                    </p>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
    </div>
</div><?php /**PATH C:\Users\HeliaHaghighi\Desktop\MileleSkillSage\resources\views/livewire/test-monitoring.blade.php ENDPATH**/ ?>