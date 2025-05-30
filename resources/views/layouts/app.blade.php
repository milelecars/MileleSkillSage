<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- For Safari -->
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="Milele SkillSage">
        <meta http-equiv="ScreenOrientation" content="autoRotate:disabled">


        <title>{{ config('app.name', 'Milele SkillSage') }}</title>

        <!-- Fonts and External CSS -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.css" rel="stylesheet">

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

        <style>
        [x-cloak] { display: none !important; }
        </style>


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

        <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

        <!-- External Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@3.11.0/dist/tf.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/coco-ssd@2.2.2/dist/coco-ssd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>
        <!-- <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script> -->

        <!-- Application Assets -->
        @if (app()->environment('local'))
            @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/webcam.js', 'resources/js/test-monitoring.js'])
        @else
            <link rel="stylesheet" href="/build/assets/app-hipL5E8m.css">
            <script src="/build/assets/app-Dh5OhEi1.js" defer></script>
            <script src="/build/assets/webcam-k5lZS9Xj.js" defer></script>
            <script src="/build/assets/test-monitoring-Hrfa2zOF.js" defer></script>
        @endif

        @livewireStyles
    </head>

    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        @livewireScripts
    </body>
</html>
