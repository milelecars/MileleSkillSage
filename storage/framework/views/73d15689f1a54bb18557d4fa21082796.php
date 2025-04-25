<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <!-- For Safari -->
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="Milele SkillSage">
        <meta http-equiv="ScreenOrientation" content="autoRotate:disabled">


        <title><?php echo e(config('app.name', 'Milele SkillSage')); ?></title>

        <!-- Fonts and External CSS -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">
        <link src="/build/assets/flowbite.min.css" rel="stylesheet" >

        <!-- Initialize monitoring data before any scripts -->
        <script>
            window.monitoringData = {
                metrics: {
                    tabSwitches: 0,
                    windowBlurs: 0,
                    mouseExits: 0,
                    copyCutAttempts: 0,
                    rightClicks: 0,
                    keyboardShortcuts: 0,
                    warningCount: 0
                }
            };
        </script>

        <!-- Device detection -->
        <!-- <script>
            function blockMobileAccess() {
                const mobileDevices = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i;
                if (mobileDevices.test(navigator.userAgent) && window.innerWidth <= 768) {
                    document.body.innerHTML = `
                        <div style="text-align: center; padding: 20px; font-family: Arial, sans-serif;">
                            <h1>Desktop Only</h1>
                            <p>Please access this test on a laptop or desktop computer.</p>
                        </div>
                    `;
                }
            }

            window.onload = blockMobileAccess;
        </script> -->

        <!-- External Scripts -->
        <link rel="preload" src="/build/assets/tf.min.js" as="script"></link>
        <link rel="preload" src="/build/assets/coco-ssd.min.js" as="script"></link>
        <link rel="preload" src="/build/assets/flowbite.min.js" as="script"></link>
        <!-- <link src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" as="script" defer></link> -->

        <!-- Application Assets -->
        <?php if(app()->environment('local')): ?>
            <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js', 'resources/js/webcam.js', 'resources/js/test-monitoring.js']); ?>
        <?php else: ?>
            <link rel="stylesheet" href="/build/assets/app-CDbIhxdv.css">
            <link rel="preload" src="/build/assets/app-Dh5OhEi1.js" as="script" defer></link>
            <link rel="preload" src="/build/assets/webcam-DRWO0hBV.js" as="script" defer></link>
            <link rel="preload" src="/build/assets/test-monitoring-D6EdK5FA.js" as="script" defer></link>
        <?php endif; ?>

        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    </head>

    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            <?php echo $__env->make('layouts.navigation', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <!-- Page Heading -->
            <?php if(isset($header)): ?>
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <?php echo e($header); ?>

                    </div>
                </header>
            <?php endif; ?>

            <!-- Page Content -->
            <main>
                <?php echo e($slot); ?>

            </main>
        </div>
        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    </body>
</html>
<?php /**PATH C:\Users\HeliaHaghighi\Desktop\MileleSkillSage\resources\views/layouts/app.blade.php ENDPATH**/ ?>