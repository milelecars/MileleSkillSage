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
    <style>
        /* selection prevention */
        body {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        /* Allow selection for specific elements where needed (e.g., form inputs) */
        input, textarea {
            -webkit-user-select: te xt;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }

        /* Additional protection against selection */
        .no-select {
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        /* Prevent drag-and-drop */
        * {
            -webkit-user-drag: none;
            -khtml-user-drag: none;
            -moz-user-drag: none;
            -o-user-drag: none;
            user-drag: none;
        }
    </style>
    
    <div class="min-h-screen bg-gray-100">
        <input type="hidden" id="test-id" value="<?php echo e($test->id); ?>">
        <input type="hidden" id="candidate-id" value="<?php echo e($candidate->id); ?>">
        
        
        <div class="rounded-lg overflow-hidden bg-gray-50 p-4 hidden">
            <video id="video" class="w-full h-auto  rounded-lg shadow-inner border-2 border-gray-200" autoplay playsinline></video>
            <div id="detection-status" class="mt-3 text-sm text-gray-600"></div>
        </div>

        <!-- Fixed Timer Bar -->
        <div class="w-full flex flex-col gap-3 items-center justify-center my-8">
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('test-timer', ['testId' => $test->id]);

$__html = app('livewire')->mount($__name, $__params, 'lw-2837484294-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
        </div>
            
            
        <!-- Main Content -->
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col items-center">
            <div class="bg-white rounded-lg shadow-md overflow-hidden w-full">
                <div class="md:flex">
                    <!-- Question Section -->
                    <div class="md:w-2/3 p-6 border-r">
                        <div class="mb-4 text-sm text-gray-600">
                            Question <?php echo e($currentQuestionIndex + 1); ?> of <?php echo e($questions->count()); ?>

                        </div>
                        <h2 class="text-xl font-medium mb-6">
                            <?php echo e($questions[$currentQuestionIndex]->question_text); ?>  
                        </h2>

                        
                        <?php if($questions[$currentQuestionIndex]->media && $questions[$currentQuestionIndex]->media instanceof \Illuminate\Database\Eloquent\Collection): ?>
                            <?php $__currentLoopData = $questions[$currentQuestionIndex]->media; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($media->image_url): ?>
                                    <img src="<?php echo e($media->image_url); ?>" 
                                        alt="<?php echo e($media->description ?? 'Question Image'); ?>" 
                                        class="mb-6 max-w-full rounded-lg">
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php elseif($questions[$currentQuestionIndex]->media && isset($questions[$currentQuestionIndex]->media->image_url)): ?>
                            <img src="<?php echo e($questions[$currentQuestionIndex]->media->image_url); ?>" 
                                alt="<?php echo e($questions[$currentQuestionIndex]->media->description ?? 'Question Image'); ?>" 
                                class="mb-6 max-w-full rounded-lg">
                        <?php endif; ?>
                    </div>

                    <!-- Choices -->
                    <div class="md:w-1/3 p-6 bg-gray-50">
                        <form method="POST" action="<?php echo e(route('tests.next', ['id' => $test->id])); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="current_index" value="<?php echo e($currentQuestionIndex); ?>">

                            
                            <div class="space-y-4">
                            <?php $__currentLoopData = $questions[$currentQuestionIndex]->choices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $choice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="flex items-start p-3 rounded-lg border border-gray-200 hover:bg-gray-100 cursor-pointer">
                                    <input type="radio" 
                                        name="answer" 
                                        value="<?php echo e($choice->id); ?>" 
                                        class="mt-1 form-radio text-blue-600" 
                                        <?php echo e(session()->get("test_session.answers.$currentQuestionIndex") === $choice->id ? 'checked' : ''); ?>

                                        required>
                                    <span class="ml-3">
                                        <span class="font-medium"><?php echo e(chr(65 + $loop->index)); ?>.</span> 
                                        <?php echo e($choice->choice_text); ?> 
                                    </span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


                            </div>

                            
                            <div class="mt-6">
                                <button type="submit" 
                                        class="w-full text-white py-3 px-6 rounded-lg 
                                        <?php echo e($currentQuestionIndex === $questions->count() - 1 ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'); ?>">
                                    <?php echo e($currentQuestionIndex === $questions->count() - 1 ? 'Submit Test' : 'Next Question'); ?>

                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

                
                <!-- Progress Bar -->
                <div class="h-1.5 bg-blue-100 w-[25%] mt-5">
                    <div class="h-full bg-blue-600 rounded-full" style="width: <?php echo e(($currentQuestionIndex + 1) / count($questions) * 100); ?>%"></div>
                </div>



                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('test-monitoring', ['testSessionId' => $test->id]);

$__html = app('livewire')->mount($__name, $__params, 'lw-2837484294-1', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

            </div>
        </div>
    </div>
    <script src="<?php echo e(asset('js/webcam.js')); ?>"></script>
    <script src="<?php echo e(asset('js/test-monitoring.js')); ?>"></script>
    <!-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            disableCopyPaste();
            // disableRightClick();
            disableKeyboardShortcuts();
        });
    
        function disableCopyPaste() {
            document.addEventListener('copy', function(e) {
                e.preventDefault();
            });
    
            document.addEventListener('cut', function(e) {
                e.preventDefault();
            });
    
            document.addEventListener('paste', function(e) {
                e.preventDefault();
            });
        }

        // function disableRightClick() {
        //     document.addEventListener('contextmenu', function(e) {
        //         e.preventDefault();
        //     });
        // }

        function disableKeyboardShortcuts() {
            document.addEventListener('keydown', function(e) {
                // Prevent Ctrl+C, Ctrl+V, Ctrl+X
                if ((e.ctrlKey || e.metaKey) && (e.key === 'c' || e.key === 'v' || e.key === 'x')) {
                    e.preventDefault();
                }
                
                // Prevent F12 key (Developer Tools)
                if (e.key === 'F12') {
                    e.preventDefault();
                }

                // Prevent Ctrl+Shift+I (Developer Tools)
                if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'i') {
                    e.preventDefault();
                }

                // Prevent Ctrl+Shift+C (Developer Tools Element Inspector)
                if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'c') {
                    e.preventDefault();
                }

                // Prevent Alt+Text Selection
                if (e.altKey) {
                    e.preventDefault();
                }
            });
        }
    </script> -->
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\Users\HeliaHaghighi\Desktop\MileleSkillSage\resources\views/tests/start.blade.php ENDPATH**/ ?>