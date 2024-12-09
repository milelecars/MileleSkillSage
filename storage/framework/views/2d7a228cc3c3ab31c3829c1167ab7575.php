<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div 
        x-data="{ show: <?php if(session('warning')): ?> true <?php else: ?> false <?php endif; ?> }"
        x-show="show"
        x-init="setTimeout(() => { show = false }, 5000)"
        class="fixed top-4 right-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded shadow-lg"
        style="z-index: 50;"
    >
        <div class="flex">
            <div class="py-1"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg></div>
            <div class="ml-3">
                <p class="font-medium"><?php echo e(session('warning')); ?></p>
            </div>
            <div class="pl-3">
                <button @click="show = false" class="text-yellow-700 hover:text-yellow-900">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    <div class="py-12 text-theme bg-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="p-6 sm:p-10">
                    <h1 class="text-3xl font-bold text-gray-900 mb-6">Test Results</h1>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-20 rounded-lg">
                        <p class="text-blue-700">Thank you for completing the test!</p>
                    </div>


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h2 class="text-xl font-semibold mb-3">Test Information</h2>
                            <ul class="space-y-2">
                                <li><strong>Test Name:</strong> <?php echo e($test->title); ?></li>
                                <li><strong>Started At:</strong>
                                    <?php if($testAttempt->pivot->started_at): ?>
                                        <?php echo e(\Carbon\Carbon::parse($testAttempt->pivot->started_at)->format('M d, Y H:i:s')); ?>

                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </li>
                                <li><strong>Completed At:</strong>
                                    <?php if($testAttempt->pivot->completed_at): ?>
                                        <?php
                                            $startedAt = \Carbon\Carbon::parse($testAttempt->pivot->started_at);
                                            $completedAt = \Carbon\Carbon::parse($testAttempt->pivot->completed_at);
                                            $expectedEndTime = $startedAt->copy()->addMinutes($test->duration);
                                        ?>
                                        <?php if($completedAt->gt($expectedEndTime)): ?>
                                            <?php echo e($expectedEndTime->format('M d, Y H:i:s')); ?>

                                        <?php else: ?>
                                            <?php echo e($completedAt->format('M d, Y H:i:s')); ?>

                                        <?php endif; ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </li>

                                <li>
                                    <strong>Duration:</strong>
                                    <?php if($testAttempt->pivot->started_at && $testAttempt->pivot->completed_at): ?>
                                        <?php
                                            $startedAt = \Carbon\Carbon::parse($testAttempt->pivot->started_at);
                                            $completedAt = \Carbon\Carbon::parse($testAttempt->pivot->completed_at);
                                            $duration = $startedAt->diff($completedAt);
                                            $durationInMinutes = $duration->days * 24 * 60 + $duration->h * 60 + $duration->i;
                                            $durationInSeconds = $duration->s;
                                        ?>
                                        <?php echo e($durationInMinutes); ?> <?php echo e(Str::plural('minute', $durationInMinutes)); ?> and <?php echo e($durationInSeconds); ?> <?php echo e(Str::plural('second', $durationInSeconds)); ?>

                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </li>

                            </ul>
                        </div>
                        
                        <div>
                            <h2 class="text-xl font-semibold mb-3">Score Summary</h2>
                            <div class="bg-gray-100 p-4 rounded-lg">
                                <div class="text-4xl font-bold text-center text-blue-600">
                                    <?php echo e($testAttempt->pivot->score ?? 0); ?> / <?php echo e(count($questions ?? [])); ?>

                                </div>
                                <p class="text-center text-gray-600 mt-2">Correct Answers</p>
                            </div>
                            <div class="mt-4">
                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?php echo e(($candidate->test_score / count($questions) * 100)); ?>%"></div>
                                </div>
                                <p class="text-center text-gray-600 mt-2">
                                <?php echo e(round(($testAttempt->pivot->score / count($questions)) * 100, 1)); ?>% Score
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10">
                        <h2 class="text-xl font-semibold mb-3">🌟Performance Feedback🌟</h2>
                        <p class="text-gray-700">
                            <?php if(($testAttempt->pivot->score/ count($questions)) >= 0.8): ?>
                                Excellent work! Your high score demonstrates a strong understanding of the subject matter.
                            <?php elseif(($testAttempt->pivot->score/ count($questions)) >= 0.6): ?>
                                Good job! You've shown a solid grasp of many key concepts, but there's still room for improvement.
                            <?php else: ?>
                                Thank you for completing the test. We recommend further study to improve your understanding of the material.
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="mt-20 flex justify-end w-full">
                        <a href="<?php echo e(route('candidate.dashboard')); ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold  text-white hover:bg-blue-500 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Return to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\Users\HeliaHaghighi\Desktop\MileleSkillSage\resources\views\tests\result.blade.php ENDPATH**/ ?>