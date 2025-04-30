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
            <?php if(session('success')): ?>
                <div class="mb-4 p-4 pl-6 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>
            <?php if($errors->any()): ?>
                <div class="mb-4 p-4 bg-red-50 border border-red-500 text-red-700 rounded-lg">
                    <ul class="pl-5">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="p-4 sm:p-6 md:p-8">
                    
                    <div class="mb-8">
                        <h1 class="text-lg md:text-2xl font-extrabold text-gray-900 mb-2">Invite Candidates</h1>
                        <p class="text-sm md:text-base text-gray-600">Select a candidate and choose tests to send invitations.</p>
                    </div>

                    
                    <form action="<?php echo e(route('admin.select-candidate')); ?>" method="GET" class="mb-8">
                        <div class="mb-6">
                            <label for="candidate" class="text-base md:text-lg font-semibold text-gray-800">Select Candidate</label>

                            
                            <select 
                                name="selected_email" 
                                class="text-sm md:text-base w-full border border-gray-300 rounded-md shadow-sm p-2.5 mt-4 focus:ring-blue-500 focus:border-blue-500" 
                                onchange="this.form.submit()"
                            >
                                <option value="">Search by email...</option>
                                <?php $__currentLoopData = $emailToTestIds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $email => $testIds): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($email); ?>" <?php echo e(request('selected_email') == $email ? 'selected' : ''); ?>>
                                        <?php echo e($email); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <!-- <div class="mb-6">
                            
                            <label for="candidate_name" class="text-sm md:text-base md:text-lg font-semibold text-gray-800">Search by Name</label>
                            <input 
                                type="text" 
                                name="candidate_name" 
                                value="<?php echo e(request('candidate_name')); ?>" 
                                class="w-full border border-gray-300 rounded-md shadow-sm p-2.5 mt-4 focus:ring-blue-500 focus:border-blue-500" 
                                placeholder="Enter candidate name..."
                                oninput="this.form.submit()"
                            >
                        </div> -->
                    </form>


                    <?php if(request('selected_email')): ?>
                        <form action="<?php echo e(route('admin.send')); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="email_test_map[<?php echo e(request('selected_email')); ?>]" value="">

                            
                            <div class="mb-8">
                                <h4 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Already Invited Tests</h4>
                                <div class="bg-gray-50 rounded-lg p-6">
                                    <div class="space-y-3">
                                        <?php $__empty_1 = true; $__currentLoopData = $emailToTestIds[request('selected_email')] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $testId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <div class="text-sm md:text-base flex items-center text-gray-700 bg-white p-3 rounded-md shadow-sm">
                                                <svg class="h-5 w-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <?php echo e($tests->find($testId) ? $tests->find($testId)->title : ''); ?>

                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <p class="text-gray-500 italic">No previous invitations</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="mb-6">
                                <label for="role" class="text-base md:text-lg font-semibold text-gray-800">Enter Role</label>
                                <input 
                                    type="text" 
                                    name="role" 
                                    id="role" 
                                    class="placeholder:text-sm text-sm md:text-base w-full border border-gray-300 rounded-md shadow-sm p-2.5 mt-2 focus:ring-blue-500 focus:border-blue-500" 
                                    placeholder="Enter candidate's role..." 
                                    required
                                >
                            </div>

                            
                            <div class="mb-8">
                                <h4 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Select Tests to Invite</h4>
                                <div class="bg-gray-50 rounded-lg p-6">
                                    <div class="space-y-3">
                                        <?php $__empty_1 = true; $__currentLoopData = $emailToUninvitedTestIds[request('selected_email')] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $testId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <div class="flex items-center bg-white p-3 rounded-md shadow-sm hover:bg-gray-50 transition-colors">
                                                <input 
                                                    type="checkbox" 
                                                    id="test_<?php echo e($testId); ?>" 
                                                    name="email_test_map[<?php echo e(request('selected_email')); ?>][]" 
                                                    value="<?php echo e($testId); ?>"
                                                    class="h-4 w-4 text-blue-700 border-gray-300 rounded-lg "
                                                >
                                                <label 
                                                    for="test_<?php echo e($testId); ?>" 
                                                    class="text-sm md:text-base ml-3 block text-gray-700 cursor-pointer flex-1"
                                                >
                                                    <?php echo e($tests->find($testId) ? $tests->find($testId)->title : ''); ?>

                                                </label>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <p class="text-gray-500 italic">No available tests to invite</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            
                            <?php if(!empty($emailToUninvitedTestIds[request('selected_email')])): ?>
                                <div class="flex justify-end">
                                    <button type="submit" class="text-xs inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white  tracking-widest hover:bg-blue-700 disabled:opacity-25 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 md:w-5 md:h-5 mr-2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                                        </svg>
                                        Send
                                    </button>
                                </div>
                            <?php endif; ?>
                        </form>
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
<?php endif; ?><?php /**PATH C:\Users\HeliaHaghighi\Desktop\MileleSkillSage\resources\views/admin/invite.blade.php ENDPATH**/ ?>