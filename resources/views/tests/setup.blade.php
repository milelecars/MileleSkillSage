<x-app-layout>
    <div class="text-theme" id="dashboard-container">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- header --}}
            <div class="p-6 pb-4 mt-5 mb-10 border-b-2 border-gray-800">
                <h1 class="text-xl md:text-3xl font-bold text-gray-900">
                    Welcome, {{ Auth::guard('candidate')->user()->name}} 👋
                </h1>
                <div class="text-xs sm:text-sm text-gray-600 mt-2 flex items-start sm:items-center gap-1 sm:gap-2 flex-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                    </svg>
                    {{ Auth::guard('candidate')->user()->email }}
                </div>
            </div>

            {{-- content --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mx-2 sm:mx-4 pb-6 sm:pb-8">
                {{-- Camera Section --}}
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

                {{-- right --}}
                <div class="grid gap-4 text-justify">
                    {{-- Camera Warning --}}
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

                    {{-- Troubleshooting Guide --}}
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


                    @if(isset($test))
                        <div class="mb-6 text-sm md:text-base">
                            @if($testAttempt && $testAttempt->pivot->status == "completed")
                                {{-- Test completed --}}
                                <div class="flex justify-between gap-4 items-center bg-green-100 border-l-4 border-green-500 rounded-lg p-4 mb-4">
                                    <p class="text-green-700">You have completed this test.</p>
                                    <a href="{{ route('tests.result', ['id' => $test->id]) }}"
                                    class="text-sm md:text-base md:inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                        View Results
                                    </a>
                                </div>
                            @elseif($testAttempt && $testAttempt->pivot->status == "in progress")
                                {{-- Test in progress --}}
                                <div class="flex justify-between gap-4 items-center bg-blue-100 border-l-4 border-blue-500 rounded-lg p-4 mb-4">
                                    <p class="text-blue-700">You have a test in progress.</p>
                                    <a href="{{ route('tests.start', ['id' => $test->id]) }}"
                                    class="text-sm md:text-base md:inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                        Continue Test
                                    </a>
                                </div>
                            @else
                                {{-- Test not started --}}
                                <div class="flex w-full justify-between gap-4 items-center bg-gray-100 border-l-4 border-blue-600 rounded-lg p-4 pr-0 mb-4">
                                    <p class="text-gray-700">Please review the guidelines before starting the test.</p>
                                    <a href="{{ route('tests.show', ['id' => $test->id]) }}"    onclick="window.__PRESERVE_STREAM__ = true;" 
                                    class="text-sm md:text-base md:inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                        View Guidelines
                                    </a>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 rounded-lg">
                            <p class="text-yellow-700">No test is currently available. <br> Please check your invitation or contact the administrator.</p>
                        </div>
                    @endif
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
                const deviceId = localStorage.getItem('camera_device_id');
                const constraints = {
                    video: deviceId ? { deviceId: { exact: deviceId } } : { facingMode: "user" },
                    audio: false
                };

                navigator.mediaDevices.getUserMedia(constraints)
                .then(function(stream) {
                    video.srcObject = stream;
                    video.play();
                    detectionStatus.innerText = "Camera connected successfully.";

                    const track = stream.getVideoTracks()[0];
                    const settings = track.getSettings();
                })
                .catch(function(error) {
                    console.error("Camera access error:", error);
                    detectionStatus.innerText = "Camera access was denied or not available.";
                });
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

        // Mobile-optimized camera permission handler
        document.addEventListener('DOMContentLoaded', function() {
            // Check if we're on a mobile device
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
            
            // Only run this script on mobile devices
            if (!isMobile) return;
            
            console.log("Mobile device detected, initializing optimized camera handler");
            
            // Get required elements
            const video = document.getElementById('video');
            const detectionStatus = document.getElementById('detection-status');
            
            if (!video || !detectionStatus) {
                console.log("Required video elements not found");
                return;
            }
            
            // Create persistent permission tracking
            let permissionRequested = false;
            let streamActive = false;
            
            // Override the original WebcamManager's requestCameraAccess method if it exists
            if (window.webcamManager && window.webcamManager.requestCameraAccess) {
                const originalMethod = window.webcamManager.requestCameraAccess;
                window.webcamManager.requestCameraAccess = function() {
                    console.log("Intercepting original requestCameraAccess call");
                    return mobileRequestCamera.call(this);
                };
            }
            
            // Add a button to manually trigger camera permission
            function addCameraButton() {
                // Remove any existing camera buttons first
                const existingButtons = document.querySelectorAll('.mobile-camera-btn');
                existingButtons.forEach(btn => btn.remove());
                
                const cameraBtn = document.createElement('button');
                cameraBtn.textContent = "Allow Camera Access";
                cameraBtn.className = "mobile-camera-btn px-4 py-3 bg-blue-600 text-white rounded-md w-full mb-4";
                cameraBtn.style.cssText = "background-color: #2563eb; color: white; padding: 12px 16px; border-radius: 8px; font-weight: bold; margin: 10px 0; width: 100%; border: none;";
                
                // Insert before the video element or status display
                const container = video.parentElement || detectionStatus.parentElement;
                if (container) {
                    container.insertBefore(cameraBtn, container.firstChild);
                } else {
                    // Fallback - add to body
                    document.body.insertBefore(cameraBtn, document.body.firstChild);
                }
                
                cameraBtn.addEventListener('click', function() {
                    mobileRequestCamera();
                    // Don't remove the button immediately in case permission fails
                });
            }
            
            // Mobile-optimized camera request function
            async function mobileRequestCamera() {
                console.log("Mobile camera request initiated");
                
                try {
                    // Always request fresh permissions on mobile - don't use stored deviceId
                    const constraints = {
                        video: { facingMode: "user" },
                        audio: false
                    };
                    
                    // Explicitly request permission
                    const stream = await navigator.mediaDevices.getUserMedia(constraints);
                    
                    // Successfully got a stream
                    permissionRequested = true;
                    streamActive = true;
                    
                    
                    // Get track info to store deviceId
                    const videoTrack = stream.getVideoTracks()[0];
                    if (videoTrack) {
                        const settings = videoTrack.getSettings();
                        const deviceId = settings.deviceId;
                        
                    }
                    
                    // Attach stream to video element
                    video.srcObject = stream;
                    
                    // Remove the manual button if it exists
                    const manualBtn = document.querySelector('.mobile-camera-btn');
                    if (manualBtn) manualBtn.remove();
                    
                    // Handle loaded metadata and play
                    return new Promise((resolve) => {
                        video.onloadedmetadata = () => {
                            video.play()
                            .then(() => {
                                detectionStatus.innerHTML = "<p style='color: green;'>Camera connected successfully</p>";
                                console.log("Camera successfully connected and playing");
                                
                                // If we're part of WebcamManager, continue with detection
                                if (window.webcamManager && window.webcamManager.initializeDetection) {
                                    window.webcamManager.initializeDetection();
                                }
                                
                                resolve(stream);
                            })
                            .catch(err => {
                                console.error("Error playing video:", err);
                                detectionStatus.innerHTML = "<p style='color: orange;'>Camera connected but couldn't autoplay. Please check your device settings.</p>";
                                resolve(stream);
                            });
                        };
                        
                        // Handle potential timeout
                        setTimeout(() => {
                            if (video.readyState < 2) { // HAVE_CURRENT_DATA
                                console.warn("Video metadata loading timeout - forcing play attempt");
                                video.play().catch(e => console.error("Timeout play error:", e));
                                resolve(stream);
                            }
                        }, 3000);
                    });
                    
                } catch (error) {
                    console.error("Mobile camera request error:", error);
                    permissionRequested = true;
                    streamActive = false;
                    
                    // User denied permission - clear any old permissions
                    if (error.name === "NotAllowedError") {
                        localStorage.removeItem('camera_permission_granted');
                        sessionStorage.removeItem('camera_permission_granted');
                        detectionStatus.innerHTML = "<p style='color: red;'>Camera access was denied. Please click 'Allow' when prompted and refresh the page.</p>";
                    } else {
                        detectionStatus.innerHTML = "<p style='color: red;'>Camera error: " + error.message + "</p>";
                    }
                    
                    // Always show the manual button after error
                    addCameraButton();
                    
                    throw error;
                }
            }
            
            // Check if permission was previously granted
            const previouslyGranted = 
                localStorage.getItem('camera_permission_granted') === 'yes' ||
                sessionStorage.getItem('camera_permission_granted') === 'yes';
            
            // For iOS, always show the button first since permissions are more restrictive
            if (isIOS || !previouslyGranted) {
                console.log("Adding manual camera button for iOS or first-time user");
                addCameraButton();
            } else {
                // Try auto-starting for non-iOS devices with previous permission
                mobileRequestCamera().catch(err => {
                    console.error("Auto-start failed:", err);
                    addCameraButton();
                });
            }
            
            // Monitor camera status and reconnect if needed
            setInterval(() => {
                if (permissionRequested && !streamActive && video) {
                    const stream = video.srcObject;
                    if (!stream || !stream.active) {
                        console.log("Stream inactive, attempting to reconnect camera");
                        mobileRequestCamera().catch(e => console.error("Reconnect failed:", e));
                    }
                }
            }, 5000);
        });


        console.log("Device ID (sessionStorage):", sessionStorage.getItem('camera_device_id'));
        console.log("Device ID (Laravel):", this.deviceId);
        console.log("Permission granted:", this.permissionGranted);
        console.log("Stream active:", this.stream?.active);
        console.log("window.__ACTIVE_STREAM__ present:", !!window.__ACTIVE_STREAM__);

    </script>

</x-app-layout>