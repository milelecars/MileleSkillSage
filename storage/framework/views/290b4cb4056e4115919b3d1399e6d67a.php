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
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h1 class="text-xl md:text-2xl font-bold text-gray-800">
                        Welcome, <?php echo e(Auth::guard('web')->user()->name); ?> 👋
                    </h1>
                    <div class="text-sm text-gray-500 mt-1">
                        <?php echo e(Auth::guard('web')->user()->email); ?>

                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-lg md:text-xl font-semibold text-gray-800 mb-4">Quick Actions</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="<?php echo e(route('tests.index')); ?>" class="block p-6 bg-blue-100 hover:bg-blue-200 rounded-lg transition duration-300">
                            <h3 class="text-md md:text-lg font-semibold text-blue-700">Manage Tests</h3>
                            <p class="text-blue-600 mt-2 text-sm md:text-base">View, create, and edit assessment tests</p>
                        </a>
                        <a href="<?php echo e(route('admin.manage-candidates')); ?>" class="block p-6 bg-green-100 hover:bg-green-200 rounded-lg transition duration-300">
                            <h3 class="text-md md:text-lg font-semibold text-green-700">Manage Candidates</h3>
                            <p class="text-green-600 mt-2 text-sm md:text-base">View test results, approve/reject candidates</p>
                        </a>
                        <a href="<?php echo e(route('admin.manage-reports')); ?>" class="block p-6 bg-purple-100 hover:bg-purple-200 rounded-lg transition duration-300">
                            <h3 class="text-md md:text-lg font-semibold text-purple-700">Manage Reports</h3>
                            <p class="text-purple-600 mt-2 text-sm md:text-base">Generate and manage reports</p>
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
<?php endif; ?><?php /**PATH C:\Users\HeliaHaghighi\Desktop\MileleSkillSage\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>