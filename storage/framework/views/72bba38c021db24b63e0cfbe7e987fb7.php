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
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <h1 class="text-2xl font-bold">Edit Test</h1>
                        <a href="<?php echo e(url()->previous() == route('tests.index') ? route('tests.index') : route('tests.show', $test->id)); ?>"
                            class="text-blue-700 hover:text-blue-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </a>
                    </div>
                    <form action="<?php echo e(route('tests.update', $test->id)); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <div class="mb-4">
                            <label for="title" class="block text-gray-700 text-md font-bold mb-2">Test Title</label>
                            <input type="text" name="title" id="title" value="<?php echo e(old('title', $test->title)); ?>" class="appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        </div>
                        <div class="mb-4">
                            <label for="duration" class="block text-gray-700 text-md font-bold mb-2">Test Duration</label>
                            <input type="text" name="duration" id="duration" value="<?php echo e(old('duration', $test->duration)); ?>" class="appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        </div>
                        <div class="mb-4">
                            <label for="description" class="block text-gray-700 text-md font-bold mb-2">Description:</label>
                            <textarea name="description" id="description" rows="4" class="appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?php echo e(old('description', $test->description)); ?></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="file" class="block text-gray-700 text-md font-bold mb-2">Import Questions (Optional)</label>
                            <input type="file" name="file" accept=".xlsx,.csv,.json" class="rounded-lg border border-neutral-500">
                        </div>
                        <div class="flex items-center justify-end">
                            <button type="submit" class="bg-blue-700 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline">
                                Update Test
                            </button>
                        </div>
                    </form>                  
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
<?php endif; ?><?php /**PATH C:\Users\HeliaHaghighi\Desktop\MileleSkillSage\resources\views/tests/edit.blade.php ENDPATH**/ ?>