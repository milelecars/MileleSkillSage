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
    <div class="py-12 text-theme">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="p-6">
                    <h1 class="text-3xl font-bold text-gray-900 mb-6">Manage Reports</h1>

                    <?php if(session('success')): ?>
                        <div class="bg-amber-50 border-l-4 border-amber-200 text-amber-800 p-4 mb-4 rounded-lg">
                            <?php echo e(session('success')); ?>

                        </div>
                    <?php endif; ?>

                    <?php if(session('error')): ?>
                        <div class="bg-red-50 border-l-4 border-red-200 text-red-800 p-4 mb-4 rounded-lg">
                            <?php echo e(session('error')); ?>

                        </div>
                    <?php endif; ?>

                    <!-- Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-blue-700">Total Tests</h3>
                            <p class="text-2xl font-bold text-blue-900"><?php echo e($totalTests); ?></p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-green-700">Total Reports</h3>
                            <p class="text-2xl font-bold text-green-900"><?php echo e($totalReports); ?></p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-700">Total Candidates Participated</h3>
                            <p class="text-2xl font-bold text-purple-900"><?php echo e($totalCandidatesParticipated); ?></p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-700">Completed Tests</h3>
                            <p class="text-2xl font-bold text-gray-900">2</p>
                        </div>
                    </div>

                    <!-- Reports Table -->
                    <div class="overflow-x-auto rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 border border-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Test Title</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Progress</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Invitation Expiry</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Total Reports</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $__empty_1 = true; $__currentLoopData = $testReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr class="text-center">
                                        <td class="px-2 py-4 text-sm"><?php echo e($report->title); ?></td>
                                        <td class="px-2 py-4 text-sm">
                                            <div class="relative w-full h-6 bg-gray-200 rounded-full overflow-hidden flex">
                                                <!-- candidates participated  -->
                                                <div class="bg-green-200 h-full relative flex items-center justify-center"
                                                    style="width: <?php echo e(($report->total_candidates_invited > 0 ? ($totalCandidatesParticipated / $report->total_candidates_invited) * 100 : 0)); ?>%">
                                                    <span class="text-xs font-medium text-gray-700"><?php echo e($totalCandidatesParticipated); ?></span>
                                                </div>
                                                
                                                <!-- candidates invited -->
                                                <div class="bg-yellow-100 h-full relative flex items-center justify-center"
                                                    style="width: <?php echo e(($report->total_candidates_invited > 0 ? (($report->total_candidates_invited - $totalCandidatesParticipated) / $report->total_candidates_invited) * 100 : 0)); ?>%">
                                                    <span class="text-xs font-medium text-gray-700">
                                                        <?php echo e($report->total_candidates_invited - $totalCandidatesParticipated); ?>

                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-2 py-4 text-sm">
                                            <?php echo e(isset($report->invitation_expiry) ? date('Y-m-d', strtotime($report->invitation_expiry)) : 'N/A'); ?>

                                        </td>
                                        <td class="px-2 py-4 text-sm"><?php echo e($report->total_reports); ?></td>
                                        <td class="px-2 py-4">
                                            <div class="flex justify-center">
                                                <a href="<?php echo e(route('admin.download-test-reports', $report->id)); ?>" 
                                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                    </svg>
                                                    Download All
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                            No reports found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
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
<?php endif; ?><?php /**PATH C:\Users\HeliaHaghighi\Desktop\MileleSkillSage\resources\views\admin\manage-reports.blade.php ENDPATH**/ ?>