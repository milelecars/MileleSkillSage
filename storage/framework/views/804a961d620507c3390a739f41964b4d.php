<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to My App</title>
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/app.css'); ?>
</head>
<body class="antialiased font-sans">
    <div class="min-h-screen bg-gray-100 flex flex-col justify-center items-center">
        <img id="background" class="absolute h-screen w-screen bg-cover bg-center" src="<?php echo e(asset('images/bg.jpg')); ?>" />
        <div class="relative min-h-screen flex flex-col items-center justify-center selection:text-white">
            <a href="/" class="w-[30%] mx-auto my-2 mb-16 block object-contain">
                <?php if (isset($component)) { $__componentOriginal8892e718f3d0d7a916180885c6f012e7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8892e718f3d0d7a916180885c6f012e7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.application-logo','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('application-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $attributes = $__attributesOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $component = $__componentOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__componentOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
            </a>            
            <?php if(auth()->guard()->check()): ?>
                <?php if(Auth::guard('web')->check()): ?>
                    <!-- Admin dashboard link -->
                    <a href="<?php echo e(route('admin.dashboard')); ?>" class="text-white bg-blue-700 hover:bg-blue-600 font-bold py-2 px-4 rounded text-center focus:outline-none focus:shadow-outline">Admin Dashboard</a>
                <?php elseif(Auth::guard('candidate')->check()): ?>
                    <!-- Candidate dashboard link -->
                    <a href="<?php echo e(route('candidate.dashboard')); ?>" class="text-white bg-blue-700 hover:bg-blue-600 font-bold py-2 px-4 rounded text-center focus:outline-none focus:shadow-outline">Candidate Dashboard</a>
                <?php else: ?>
                    <!-- Default dashboard link -->
                    <a href="<?php echo e(url('/dashboard')); ?>" class="text-white bg-blue-700 hover:bg-blue-600 font-bold py-2 px-4 rounded text-center focus:outline-none focus:shadow-outline">Dashboard</a>
                <?php endif; ?>
            <?php else: ?>
                <div class="grid grid-cols-2 gap-10">
                    <!-- Login link -->
                    <a href="<?php echo e(route('login')); ?>" class="text-white bg-blue-700 hover:bg-blue-600 font-bold py-2 px-4 rounded text-center focus:outline-none focus:shadow-outline">Log in</a>
                    
                    <!-- Register link -->
                    <?php if(Route::has('register')): ?>
                        <a href="<?php echo e(route('register')); ?>" class="text-slate-900 bg-slate-200 hover:bg-slate-50 font-bold py-2 px-4 rounded text-center focus:outline-none focus:shadow-outline">Register</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        

        </div>
    </div>
</body>
</html><?php /**PATH C:\Users\HeliaHaghighi\Desktop\AGCT-Software\resources\views/welcome.blade.php ENDPATH**/ ?>