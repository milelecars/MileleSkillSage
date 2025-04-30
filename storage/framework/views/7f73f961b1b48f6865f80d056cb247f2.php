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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="p-6 pb-4 mt-5 mb-10 border-b-2 border-gray-800">
                <h1 class="text-xl md:text-3xl font-bold text-gray-900">
                    Welcome, <?php echo e(Auth::guard('candidate')->user()->name); ?> 👋
                </h1>
                <div class="text-xs sm:text-sm text-gray-600 mt-2 flex items-start sm:items-center gap-1 sm:gap-2 flex-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                    </svg>
                    <?php echo e(Auth::guard('candidate')->user()->email); ?>

                </div>
            </div>

            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mx-2 sm:mx-4 pb-6 sm:pb-8">
                
                <div class="bg-white rounded-xl p-4 sm:p-6 md:p-8">
                    <div class="mb-6">
                        <h2 class="text-base md:text-xl font-semibold text-gray-900 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Camera Setup
                        </h2>
                        <p class="text-gray-600 mt-2 leading-relaxed">
                            We use camera images to ensure fairness for everyone.<br/>
                            Make sure that you are in front of your camera.
                        </p>
                    </div>
                    
                    <div class="rounded-lg overflow-hidden bg-gray-50 p-4">
                        <video id="video" class="w-full h-auto rounded-lg shadow-inner border-2 border-gray-200" autoplay playsinline></video>
                        <div id="detection-status" class="mt-3 text-sm text-gray-600"></div>
                    </div>

                </div>

                
                <div class="grid gap-4 text-justify">
                    
                    <div id="camera-warning" class="bg-amber-50 rounded-xl p-6 border border-amber-200">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 md:h-7 md:w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                            </div>
                            <p class="text-sm text-amber-800 leading-6">
                                It seems you don't have a camera connected to your computer or your camera is blocked. To enable, click on the camera blocked icon in your browser's address bar and reload the page. If you don't enable a camera, you can still take the assessment, but then Milele Motors cannot verify fair play.
                            </p>
                        </div>
                    </div>

                    
                    <div class="bg-blue-100 rounded-xl p-6 border border-blue-800">
                        <h3 class="text-lg font-semibold text-blue-900  mb-4">Trouble with your webcam?</h3>
                        <p class="space-y-3 text-sm text-blue-800 leading-9">
                            -  Ensure you are using <strong><u>Chrome / Edge browser</u></strong>!</br>
                            -  Ensure you have granted permission for your browser to access your camera.</br>
                            -  If you have multiple camera devices, ensure you have given your browser and our website permission to use the right device.</br>
                            -  Try launching the assessment in incognito mode or in a private window.</br>
                            -  Ensure your camera drivers and web browser are up to date.</br>
                            -  Restart your device and try accessing the assessment again using the link in the invitation email.
                        </p>
                    </div>


                    <?php if(isset($test)): ?>
                        <div class="mb-6 text-sm md:text-base">
                            <?php if($testAttempt && $testAttempt->pivot->status == "completed"): ?>
                                
                                <div class="flex justify-between gap-4 items-center bg-green-100 border-l-4 border-green-500 rounded-lg p-4 mb-4">
                                    <p class="text-green-700">You have completed this test.</p>
                                    <a href="<?php echo e(route('tests.result', ['id' => $test->id])); ?>"
                                    class="text-sm md:text-base md:inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                        View Results
                                    </a>
                                </div>
                            <?php elseif($testAttempt && $testAttempt->pivot->status == "in progress"): ?>
                                
                                <div class="flex justify-between gap-4 items-center bg-blue-100 border-l-4 border-blue-500 rounded-lg p-4 mb-4">
                                    <p class="text-blue-700">You have a test in progress.</p>
                                    <a href="<?php echo e(route('tests.start', ['id' => $test->id])); ?>"
                                    class="text-sm md:text-base md:inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                        Continue Test
                                    </a>
                                </div>
                            <?php else: ?>
                                
                                <div class="flex w-full justify-between gap-4 items-center bg-gray-100 border-l-4 border-blue-600 rounded-lg p-4 pr-0 mb-4">
                                    <p class="text-gray-700">Please review the guidelines before starting the test.</p>
                                    <a href="<?php echo e(route('tests.show', ['id' => $test->id])); ?>"
                                    class="text-sm md:text-base md:inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                        View Guidelines
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 rounded-lg">
                            <p class="text-yellow-700">No test is currently available. <br> Please check your invitation or contact the administrator.</p>
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

            // Camera Setup
            const video = document.getElementById('video');
            const detectionStatus = document.getElementById('detection-status');

            function startCamera() {
                if (navigator.mediaDevices && typeof navigator.mediaDevices.getUserMedia === 'function') {
                    navigator.mediaDevices.getUserMedia({
                        video: { facingMode: "user" },
                        audio: false
                    })
                    .then(function(stream) {
                        video.srcObject = stream;
                        video.play();
                        detectionStatus.innerText = "Camera connected successfully.";

                        const track = stream.getVideoTracks()[0];
                        const settings = track.getSettings();

                        localStorage.setItem('camera_permission_granted', 'yes');
                        localStorage.setItem('camera_device_id', settings.deviceId);
                    })
                    .catch(function(error) {
                        console.error("Camera access error:", error);
                        detectionStatus.innerText = "Camera access was denied or not available. Please check your browser permissions.";
                    });
                } else {
                    detectionStatus.innerText = "Camera is not supported on this browser.";
                }
            }

            const isMobileSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
            const granted = localStorage.getItem('camera_permission_granted') === 'yes';
            const deviceId = localStorage.getItem('camera_device_id');


            if (granted && deviceId) {
                startCamera();
            } else {
                if (isMobileSafari) {
                    const startButton = document.createElement('button');
                    startButton.textContent = "Start Camera";
                    startButton.className = "mt-4 px-4 py-2 bg-blue-600 text-white rounded-md";
                    detectionStatus.parentNode.insertBefore(startButton, detectionStatus);

                    startButton.addEventListener('click', function() {
                        startButton.remove(); // Remove the button after start
                        startCamera();
                    });
                } else {
                    // Desktop Chrome/Firefox — try to auto start camera
                    startCamera();
                }
            }
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
<?php endif; ?><?php /**PATH C:\Users\HeliaHaghighi\Desktop\MileleSkillSage\resources\views/tests/setup.blade.php ENDPATH**/ ?>