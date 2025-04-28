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
    <div class="text-theme" id="dashboard-container">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
            
            <div class="p-4 sm:p-6 pb-4 mt-4 sm:mt-5 mb-6 sm:mb-10 border-b-2 border-gray-800">
                <h1 class="text-xl md:text-2xl font-bold text-gray-900">
                    Welcome, <?php echo e(Auth::guard('candidate')->user()->name); ?> 👋
                </h1>
                <div class="text-xs text-gray-600 mt-2 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                    </svg>
                    <?php echo e(Auth::guard('candidate')->user()->email); ?>

                </div>
            </div>

            
            <div class="bg-white shadow rounded-lg">
                <div class="p-4 sm:p-6">
                    <h2 class="text-base md:text-xl font-semibold mb-4">Your Tests</h2>
                    
                    <?php if($candidateTests->isEmpty()): ?>
                        <div class="text-gray-500 text-center py-4">
                            No tests available yet.
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto rounded-lg w-full block">
                            <table class="min-w-full divide-y divide-gray-200 border border-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-xs md:text-base font-semibold text-gray-500 uppercase">Test Title</th>
                                        <th class="px-4 py-3 text-xs md:text-base font-semibold text-gray-500 uppercase">Status</th>
                                        <th class="px-4 py-3 text-xs md:text-base font-semibold text-gray-500 uppercase">Started At</th>
                                        <th class="px-4 py-3 text-xs md:text-base font-semibold text-gray-500 uppercase">Completed At</th>
                                        <th class="px-4 py-3 text-xs md:text-base font-semibold text-gray-500 uppercase">Score</th>
                                        <th class="px-4 py-3 text-xs md:text-base font-semibold text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php $__currentLoopData = $candidateTests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $test): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="text-center">
                                            <td class="px-2 py-4 text-xs md:text-base">
                                                <div class="font-medium text-gray-900"><?php echo e($test['title']); ?></div>
                                                <div class="text-xs md:text-base text-gray-500"><?php echo e($test['questions_count']); ?> questions</div>
                                            </td>
                                            <td class="py-3 px-2 md:py-4 text-xs md:text-base">
                                                <span class="px-2 inline-flex text-xs md:text-base leading-5 font-semibold rounded-full 
                                                    <?php if($test['status'] === 'completed'): ?> bg-green-100 text-green-800
                                                    <?php elseif($test['status'] === 'in_progress'): ?> bg-yellow-100 text-yellow-800
                                                    <?php elseif($test['status'] === 'suspended'): ?> bg-orange-100 text-orange-800
                                                    <?php elseif($test['status'] === 'not_started'): ?> bg-gray-100 text-gray-800
                                                    <?php elseif($test['status'] === 'expired'): ?> bg-red-100 text-red-800
                                                    <?php endif; ?>">
                                                    <?php echo e(ucfirst(str_replace('_', ' ', $test['status']))); ?>

                                                </span>
                                            </td>
                                            <td class="px-2 py-4 text-xs md:text-base">
                                                <?php echo e($test['started_at'] ? \Carbon\Carbon::parse($test['started_at'])->format('M d, Y H:i') : '-'); ?>

                                            </td>
                                            <td class="px-2 py-4 text-xs md:text-base">
                                                <?php echo e($test['completed_at'] ? \Carbon\Carbon::parse($test['completed_at'])->format('M d, Y H:i') : '-'); ?>

                                            </td>
                                            <td class="px-2 py-4 text-xs md:text-base">
                                                <?php if($test['score'] !== null): ?>
                                                    <span class="font-medium">
                                                        <?php echo e($test['score']); ?><?php echo e($test['hasMCQ'] ? '%' : ''); ?>

                                                    </span>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-2 py-4">
                                                <div class="flex justify-center gap-2">
                                                    <?php if(in_array($test['status'], ['suspended',  'expired'])): ?>
                                                        <span>-</span>
                                                    <?php elseif(!in_array($test['status'], ['completed', 'accepted', 'rejected'])): ?>
                                                        <a href="<?php echo e(route('tests.setup', $test['test_id'])); ?>" class="text-blue-600 hover:text-blue-800"> 
                                                            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                                            </svg>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?php echo e(route('tests.result', $test['test_id'])); ?>" class="text-blue-600 hover:text-blue-800">
                                                            <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                            </svg>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
           
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const viewGuidelinesBtn = document.getElementById('view-guidelines-btn');
            const continueTestBtn = document.getElementById('continue-test-btn');
            const cameraWarning = document.getElementById('camera-warning');

            function updateButtonVisibility(personCount, hasBook, hasCellPhone) {
                if (viewGuidelinesBtn) {
                    viewGuidelinesBtn.style.display = (personCount === 1 && !hasBook && !hasCellPhone) ? 'inline-flex' : 'none';
                }

                if (continueTestBtn) {
                    continueTestBtn.style.display = (personCount === 1 && !hasBook && !hasCellPhone) ? 'inline-flex' : 'none';
                }

                cameraWarning.style.display = (personCount === 0) ? 'flex' : 'none';
            }

            document.addEventListener('webcamStatusUpdate', function(e) {
                updateButtonVisibility(e.detail.personCount, e.detail.hasBook, e.detail.hasCellPhone);
            });
        });

    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\Users\HeliaHaghighi\Desktop\MileleSkillSage\resources\views/candidate/dashboard.blade.php ENDPATH**/ ?>