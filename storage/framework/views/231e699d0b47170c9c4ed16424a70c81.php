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
    <div class="py-12 text-theme bg-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="p-8">
                    
                    <?php if(Auth::guard('candidate')->check()): ?>
                        <div class="rounded-lg overflow-hidden bg-gray-50 p-4 hidden">
                            <video id="video" class="w-full h-auto rounded-lg shadow-inner border-2 border-gray-200" autoplay playsinline></video>
                            <div id="detection-status" class="mt-3 text-sm text-gray-600"></div>
                        </div>
                    <?php endif; ?>

                    <div class="flex justify-between items-center mb-4">
                        <div class="flex flex-col justify-between">
                            <h1 class="text-2xl font-extrabold text-gray-900"><?php echo e($test->title); ?></h1>
                            
                        </div>  
                        <?php if(Auth::guard('web')->check()): ?>
                            <div class="flex space-x-1">
                                <a href="<?php echo e(route('tests.edit', $test->id)); ?>" class="text-yellow-500 hover:text-yellow-600 p-2 rounded-lg hover:bg-yellow-100 transition duration-150 ease-in-out" title="Edit Test">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </a>
                                <form action="<?php echo e(route('tests.destroy', $test->id)); ?>" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to archive this test? All existing data will be preserved.');">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="text-orange-500 hover:text-orange-600 p-2 rounded-lg hover:bg-orange-100 transition duration-150 ease-in-out" title="Archive Test">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if(Auth::guard('web')->check()): ?>
                        <div class="flex items-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mr-1" viewBox="0 0 24 24" width="18" height="18">
                                <path fill="#666666" d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.4 0-8-3.6-8-8s3.6-8 8-8 8 3.6 8 8-3.6 8-8 8zm.5-13H11v6l5.2 3.2.8-1.3-4.5-2.7V7z"/>
                            </svg>
                            Duration: <?php echo e($test->duration); ?>

                        </div>
                    <?php endif; ?>
                    <div class="mb-10">
                        <p class="text-lg text-gray-700 leading-relaxed text-justify">
                            <?php echo e($test->description); ?>

                        </p>

                        <?php if(Auth::guard('web')->check()): ?>
                            <a href="<?php echo e(route('tests.invite', $test->id)); ?>" class="mt-4 inline-flex items-center bg-green-600 hover:bg-green-500 text-white font-semibold px-4 py-2 rounded-md text-xs Sdisabled:opacity-25 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="19" height="19" class="mr-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                </svg>  
                                Invite
                            </a>
                        <?php endif; ?>

                    </div>

                    
                    <?php if(Auth::guard('candidate')->check()): ?>
                        <?php if($isTestCompleted): ?>
                            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 rounded-lg p-4 mb-6" role="alert">
                                <p>You have already completed this test.</p>
                                <a href="<?php echo e(route('tests.result', ['id' => $test->id])); ?>" class="font-bold underline">View Results</a>
                            </div>
                        <?php elseif($isInvitationExpired): ?>
                            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 rounded-lg p-4 mb-6" role="alert">
                                <p>The invitation for this test has expired.</p>
                            </div>
                        <?php elseif($isTestStarted): ?>
                            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 rounded-lg p-4 mb-6" role="alert">
                                <p>You have an ongoing test session.</p>
                                <a href="<?php echo e(route('tests.start', ['id' => $test->id])); ?>" class="font-bold underline">Continue Test</a>
                            </div>
                        <?php else: ?>
                            <div class="bg-yellow-100 border-l-4 border-yellow-500 rounded-lg text-yellow-700 p-4 mb-6" role="alert">
                                <p>You have <?php echo e($test->duration); ?> minutes to complete this test once you start.</p>
                            </div>
                            <div class="my-8 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                                <div class="flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#AA2E26" class="w-6 h-6 mr-2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                    </svg>
                                    <h2 class="text-xl font-bold text-red-700">Important Guidelines</h2>
                                </div>
                                <ul class="text-base text-gray-700 list-disc pl-6 space-y-3">
                                    <li><strong>One Attempt:</strong> You can take the test only once. Be prepared before starting.</li>
                                    <li><strong>Test Duration:</strong> Once started, the timer continues even if you close the browser. Complete all questions in one session.</li>
                                    <li><strong>Allowed:</strong> You are free to use a calculator, pen and paper.</li>
                                    <li><strong>No Pauses:</strong> The test cannot be paused and any interruptions won't stop the timer.</li>
                                    <li><strong>Email Requirement:</strong> Use the same email throughout. You'll need it to resume if you leave or accidentally close the web page.</li>
                                    <li><strong>Webcam Monitoring:</strong> Your webcam and audio may be monitored. Ensure it's enabled and you're alone.</li>
                                </ul>
                            </div>
                            
                            <form action="<?php echo e(route('tests.start', $test->id)); ?>" method="POST" class="mt-8">
                                <?php echo csrf_field(); ?>
                                <div class="p-2 flex items-center space-x-2">
                                    <input type="checkbox" name="agreement" id="agreement" class="rounded-lg border-black" required>
                                    <label for="agreement" class="text-sm text-gray-600">
                                        I agree to the <a href="#" class="text-blue-600 hover:underline">Terms of Service</a> and acknowledge that I have read the <a href="#" class="text-blue-600 hover:underline">Guidelines</a>
                                    </label>
                                </div>
                                <div class="flex justify-end ">
                                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                        Start Test
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 ml-2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                    

                    <?php if(Auth::guard('web')->check() || (Auth::guard('candidate')->check() && $hasMCQ)): ?>
                        <?php if($questions->count() > 0): ?>
                            <h2 class="text-2xl font-bold text-gray-800 mb-4">Test Preview</h2>
                            <div class=" space-y-8">
                                <?php $__currentLoopData = $questions->take(8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="bg-gray-50 p-6 rounded-lg shadow">
                                        <p class="text-lg mb-4 font-medium text-gray-800">
                                            <?php echo e($index + 1); ?>. <?php echo e($question->question_text); ?>

                                        </p>

                                        
                                        <?php if($hasLSQ): ?>
                                            <div class="space-y-4 flex flex-col items-center justify-center ">
                                                <input 
                                                    type="range" 
                                                    name="lsq_answers[<?php echo e($question->id); ?>]" 
                                                    min="1" 
                                                    max="5" 
                                                    step="1" 
                                                    value="<?php echo e(old('lsq_answers.' . $question->id, 3)); ?>" 
                                                    class="w-[90%] cursor-pointer"
                                                    oninput="this.nextElementSibling.value = this.value"
                                                    required
                                                >

                                                <div class="flex justify-between text-sm text-theme font-bold mt-1 px-6 w-full">
                                                    <div class="w-30 text-center -ml-6">Strongly Disagree</div>
                                                    <div class="w-16 text-center -ml-6">Disagree</div>
                                                    <div class="w-14 text-center -ml-1">Neutral</div>
                                                    <div class="w-12 text-center -mr-5">Agree</div>
                                                    <div class="w-28 text-center -mr-6">Strongly Agree</div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        
                                        
                                        <?php if($hasMCQ): ?>
                                            <?php if($question->media && $question->media instanceof \Illuminate\Database\Eloquent\Collection): ?>
                                                <?php $__currentLoopData = $question->media; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php if($media->image_url): ?>
                                                        <img src="<?php echo e($media->image_url); ?>" 
                                                            alt="<?php echo e($media->description ?? 'Question Image'); ?>" 
                                                            class="mb-4 max-w-full h-auto rounded">
                                                    <?php endif; ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php elseif($question->media && isset($question->media->image_url)): ?>
                                                <img src="<?php echo e($question->media->image_url); ?>" 
                                                    alt="<?php echo e($question->media->description ?? 'Question Image'); ?>" 
                                                    class="mb-4 max-w-full h-auto rounded border border-black">
                                            <?php endif; ?>
                                            
                                            <div class="space-y-2 ml-4 mb-4">
                                                <?php $__currentLoopData = $question->choices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $choice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="text-gray-700">
                                                        <?php echo e(chr(65 + $loop->index)); ?>. <?php echo e($choice->choice_text); ?>

                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                            
                                            <?php
                                                $correctChoice = $question->choices->firstWhere('is_correct', true);
                                                $correctIndex = $correctChoice ? $question->choices->search($correctChoice) : null;
                                            ?>
                                            <?php if($correctIndex !== null): ?>
                                                <p class="mt-4 font-semibold text-green-600">
                                                    Answer: <?php echo e(chr(65 + $correctIndex)); ?>

                                                </p>
                                            <?php endif; ?>

                                            <?php if($test->title == "General Mental Ability (GMA)" && Auth::guard('candidate')->check()): ?>
                                                <span>
                                                    <?php echo e($questionsExplained[$index]); ?>

                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                
                                <?php if(Auth::guard('web')->check()): ?>
                                    <?php if($questions->count() > 10): ?>
                                        <p class="text-gray-600 italic mt-4">
                                            Showing 10 out of <?php echo e($questions->count()); ?> questions...
                                        </p>
                                    <?php endif; ?>
                                <?php endif; ?>

                            </div>
                        <?php else: ?>
                            <p class="text-gray-600 italic">No questions available for this test.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                        
                   
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
<?php endif; ?><?php /**PATH C:\Users\HeliaHaghighi\Desktop\MileleSkillSage\resources\views/tests/show.blade.php ENDPATH**/ ?>