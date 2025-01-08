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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold mb-6">Create a New Test</h1>
            <form action="<?php echo e(route('tests.store')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-3 gap-6 bg-white shadow-md rounded-lg p-8">
                    <div class="mr-4">
                        <label for="title" class="block text-gray-700 text-md font-bold mb-2">Test Title</label>
                        <input type="text" name="title" id="title" required class="appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Enter the test title" value="<?php echo e(old('title')); ?>">
                    </div>
                    <div class="mr-4">
                        <label for="duration" class="block text-gray-700 text-md font-bold mb-2">Duration</label>
                        <input type="text" name="duration" id="duration" required class="appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Enter the duration" value="<?php echo e(old('duration')); ?>">
                    </div>
                    <div class="ml-4" wire:ignore.self>
                        <label for="invitation_link" class="block text-gray-700 text-md font-bold mb-2">Invitation Link</label>
                       
                        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('invitation-generator');

$__html = app('livewire')->mount($__name, $__params, 'lw-2430764264-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
                    </div>
                    <div class="col-span-3">
                        <label for="description" class="block text-gray-700 text-md font-bold mb-2">Description</label>
                        <textarea name="description" id="description" rows="4" class="appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Enter a description for the test"><?php echo e(old('description')); ?></textarea>
                    </div>
                    <div class="col-span-3">
                        <label for="file" class="block text-gray-700 text-md font-bold mb-2">Import Questions</label>
                        <input type="file" name="file" accept=".xlsx,.csv" required class="rounded-lg border border-neutral-500">
                    </div>
                    <div class="flex items-center justify-center col-span-3 mt-5">
                        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-600 font-bold py-2 px-4 rounded-lg text-center focus:outline-none focus:shadow-outline">Create Test</button>
                    </div>
                </div>
            </form>
                       
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
<?php endif; ?><?php /**PATH C:\Users\HeliaHaghighi\Desktop\MileleSkillSage\resources\views/tests/create.blade.php ENDPATH**/ ?>