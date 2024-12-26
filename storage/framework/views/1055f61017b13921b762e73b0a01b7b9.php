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
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white shadow-lg border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center justify-between w-full gap-4">
                            <h1 class="text-2xl font-bold text-gray-800">Active Tests</h1>

                            <div class="flex gap-2">
                                <!-- Search -->
                                <form method="GET" action="<?php echo e(route('tests.index')); ?>" class="flex gap-2">
                                    <div class="relative">
                                        <input
                                            type="text"
                                            name="search"
                                            value="<?php echo e($search ?? ''); ?>"
                                            placeholder="Search tests..."
                                            class="w-64 h-9 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                        >
                                        <?php if(isset($search) && $search): ?>
                                            <a href="<?php echo e(route('tests.index')); ?>" 
                                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                                            title="Clear search">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <button type="submit" class="px-3 h-9 text-sm font-semibold bg-blue-700 hover:bg-blue-600 text-white rounded-lg">
                                        Search
                                    </button>
                                </form>
                                
                                <!-- Dropdown menu -->
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" class="rounded-lg p-1 bg-blue-700 hover:bg-blue-600 focus:outline-none" title="More options">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" class="w-7 h-7">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z" />
                                        </svg>
                                    </button>
                                
                                    <div x-show="open" 
                                        @click.away="open = false"
                                        class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50"
                                        style="display: none;">
                                        <div class="py-1">
                                            <a href="<?php echo e(route('tests.create')); ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3 text-gray-500">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                                Add Test
                                            </a>
                                            <a href="<?php echo e(route('tests.archived')); ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3 text-gray-500">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                                </svg>
                                                Archived
                                            </a>
                                            <a href="<?php echo e(route('admin.invite')); ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-3 text-gray-500">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                                </svg>
                                                Invite
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg  bg-gray-50">
                        <ul class="divide-y divide-gray-200 rounded-lg shadow-inner">
                            <?php $__empty_1 = true; $__currentLoopData = $tests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $test): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <li class="p-5 hover:bg-gray-100 hover:rounded-lg s transition duration-150 ease-in-out ">
                                    <div class="flex justify-between items-center">
                                        <a href="<?php echo e(route('tests.show', ['id' => $test->id])); ?>" class="flex-grow">
                                            <h3 class="text-xl font-semibold text-blue-700"><?php echo e($test->title); ?></h3>
                                            <p class="text-sm text-gray-600 mt-2"><?php echo e(Str::limit($test->description, 100)); ?></p>
                                        </a>
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
                                    </div>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <li class="p-4 text-gray-500 text-center">
                                    <?php if(isset($search) && $search): ?>
                                        No tests found matching "<?php echo e($search); ?>".
                                    <?php else: ?>
                                        No active tests available.
                                    <?php endif; ?>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <!-- Pagination -->
                    <?php if($tests->hasPages()): ?>
                        <div class="mt-4">
                            <?php echo e($tests->appends(['search' => $search])->links()); ?>

                        </div>
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
<?php endif; ?><?php /**PATH C:\Users\HeliaHaghighi\Desktop\MileleSkillSage\resources\views/tests/index.blade.php ENDPATH**/ ?>