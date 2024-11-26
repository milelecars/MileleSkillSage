// public/js/webcam.js

console.log("Webcam script loaded");

class WebcamManager {

    constructor() {
        this.video = null;
        this.detectionStatus = null;
        this.model = null;
        this.stream = null;
        this.permissionGranted = false;
        this.deviceId = null;

        this.isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
        console.log("Browser is Safari:", this.isSafari);

        this.allowedRoutes = [
            '/invitation/\\w+',
            '/candidate/dashboard',
            '/tests/\\d+/',
            '/tests/\\d+/start'
        ];
        this.lastViolationTimes = {
            'More than One Person': 0,
            'Book': 0,
            'Cellphone': 0
        };
        this.violationFrameCounters = {
            'More than One Person': 0,
            'Book': 0,
            'Cellphone': 0
        };
        this.FRAME_THRESHOLD = 50; // Number of frames needed to confirm violation
        this.COOLDOWN_PERIOD = 30000; // 30 seconds in milliseconds
        
        
        // Initialize camera only after checking permissions
        this.initialize();

        // Screenshot configuration
        this.screenshotRetryAttempts = 3;
        this.screenshotRetryDelay = 5000; // 5 seconds
        this.screenshotQueue = [];
        this.isProcessingQueue = false;
        this.failedScreenshots = [];
        this.maxQueueSize = 10;
        
        // Screenshot status tracking
        this.screenshotStats = {
            attempted: 0,
            successful: 0,
            failed: 0,
            lastSuccess: null,
            lastError: null
        };
    }

    updateStatus(personCount, hasBook, hasCellPhone) {
        let statusMessage = '';
        const now = Date.now();

        // Helper function to handle violations
        const handleViolation = (condition, type) => {
            if (condition) {
                this.violationFrameCounters[type]++;
                
                if (this.violationFrameCounters[type] >= this.FRAME_THRESHOLD) {
                    const timeSinceLastViolation = now - this.lastViolationTimes[type];
                    
                    if (timeSinceLastViolation >= this.COOLDOWN_PERIOD) {
                        document.dispatchEvent(new CustomEvent('webcamViolation', {
                            detail: { violation: type }
                        }));
                        this.lastViolationTimes[type] = now;
                        console.log(`Violation recorded for ${type}`);
                    }
                    this.violationFrameCounters[type] = 0;
                }
            } else {
                this.violationFrameCounters[type] = 0;
            }
        };

        // Update status message and handle violations
        if (personCount > 1) {
            statusMessage += `<p style='color: orange;'>${personCount} people detected!</p>`;
            handleViolation(true, 'More than One Person');
        } else if (personCount === 0) {
            statusMessage += "<p style='color: red;'>No person detected!</p>";
            handleViolation(false, 'More than One Person');
        } else {
            statusMessage += "<p style='color: green;'>One person detected.</p>";
            handleViolation(false, 'More than One Person');
        }

        if (hasBook) {
            statusMessage += "<p>Book detected.</p>";
            handleViolation(true, 'Book');
        } else {
            handleViolation(false, 'Book');
        }

        if (hasCellPhone) {
            statusMessage += "<p>Cell phone detected.</p>";
            handleViolation(true, 'Cellphone');
        } else {
            handleViolation(false, 'Cellphone');
        }

        if (this.detectionStatus) {
            this.detectionStatus.innerHTML = statusMessage;
        }
    }

    async initialize() {
        try {
            const permission = await this.checkServerPermission();
            // Handle case where permission request fails
            this.permissionGranted = permission?.granted || false;
            this.deviceId = permission?.deviceId || null;
            
            console.log("Initial permission status from server:", this.permissionGranted);
            console.log("Current path:", window.location.pathname);
            
            if (this.shouldActivateCamera()) {
                await this.initializeCamera();
            }
        } catch (error) {
            console.error("Initialization error:", error);
            this.handleCameraError("Failed to initialize camera permissions");
        }
    }

    getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }

    async checkServerPermission() {
        try {
            console.log('Checking server permission...');
            const response = await fetch('/camera-permission', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin' // Important for cookies/session
            });
            
            console.log('Server response:', response);
            
            if (!response.ok) {
                throw new Error(`Server returned ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Permission data:', data);
            return data.permission || { granted: false, deviceId: null, streamActive: false };
        } catch (error) {
            console.error('Error checking server permission:', error);
            return { granted: false, deviceId: null, streamActive: false };
        }
    }
    
    async updateServerPermission(granted, deviceId = null, streamActive = false) {
        try {
            console.log('Updating server permission...', { granted, deviceId, streamActive });
            const response = await fetch('/camera-permission', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin', // Important for cookies/session
                body: JSON.stringify({
                    granted,
                    deviceId,
                    streamActive
                })
            });
    
            console.log('Update response:', response);
    
            if (!response.ok) {
                throw new Error(`Server returned ${response.status}`);
            }
    
            const data = await response.json();
            console.log('Update result:', data);
            return data.success;
        } catch (error) {
            console.error('Error updating server permission:', error);
            return false;
        }
    }

    shouldActivateCamera() {
        const currentPath = window.location.pathname;
        return this.allowedRoutes.some(route => 
            new RegExp('^' + route).test(currentPath)
        );
    }

    async initializeCamera() {
        console.log("Initializing camera for route:", window.location.pathname);
        
        this.video = document.getElementById('video');
        this.detectionStatus = document.getElementById('detection-status');

        if (!this.video || !this.detectionStatus) {
            console.error("Required elements not found");
            return;
        }

        await this.requestCameraAccess();

        // Start taking screenshots every 30 seconds
        this.startPeriodicScreenshots();
    }

    async requestCameraAccess() {
        try {
            if (this.permissionGranted && this.deviceId) {
                try {
                    this.stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            deviceId: { exact: this.deviceId }
                        },
                        audio: false
                    });
                } catch (error) {
                    console.warn("Failed to reuse device, requesting new access");
                    this.stream = await this.getNewVideoStream();
                }
            } else {
                this.stream = await this.getNewVideoStream();
            }

            await this.setupVideoStream();
        } catch (error) {
            console.error("Camera access error:", error);
            await this.updateServerPermission(false, null, false);
            this.handleCameraError(error.message || "Failed to access camera");
        }
    }

    async getNewVideoStream() {
        return await navigator.mediaDevices.getUserMedia({ 
            video: true, 
            audio: false 
        });
    }

    async setupVideoStream() {
        const videoTrack = this.stream.getVideoTracks()[0];
        if (videoTrack) {
            const settings = videoTrack.getSettings();
            await this.updateServerPermission(true, settings.deviceId, true);
        }

        this.video.srcObject = this.stream;
        
        return new Promise((resolve) => {
            this.video.onloadedmetadata = () => {
                console.log("Video metadata loaded");
                this.video.play();
                this.initializeDetection();
                this.detectionStatus.innerHTML = "<p style='color: green;'>Webcam started successfully</p>";
                
                const cameraWarning = document.getElementById('camera-warning');
                if (cameraWarning) {
                    cameraWarning.style.display = 'none';
                }
                resolve();
            };
        });
    }
    
    async preserveStream() {
        if (this.stream && this.stream.active) {
            const videoTrack = this.stream.getVideoTracks()[0];
            if (videoTrack) {
                try {
                    // Try to clone the track to keep it alive
                    const newTrack = videoTrack.clone();
                    const newStream = new MediaStream([newTrack]);
                    this.stream = newStream;
                    return true;
                } catch (error) {
                    console.error("Failed to preserve stream:", error);
                    return false;
                }
            }
        }
        return false;
    }

    async initializeDetection() {
        console.log("4")
        try {
            console.log("Loading COCO-SSD model...");
            this.model = await cocoSsd.load();
            console.log("COCO-SSD model loaded successfully");
            this.detectObjects();
        } catch (error) {
            console.error("Model initialization error:", error);
            this.detectionStatus.innerHTML += "<p style='color: red;'>Object detection unavailable</p>";
        }
    }

    async detectObjects() {
        if (!this.model || !this.video || !this.shouldActivateCamera()) {
            return;
        }

        try {
            const predictions = await this.model.detect(this.video);
            
            let personCount = 0;
            let hasBook = false;
            let hasCellPhone = false;

            predictions.forEach(prediction => {
                if (prediction.class === 'person') personCount++;
                if (prediction.class === 'book') hasBook = true;
                if (prediction.class === 'cell phone') hasCellPhone = true;
            });

            this.updateStatus(personCount, hasBook, hasCellPhone);

            document.dispatchEvent(new CustomEvent('webcamStatusUpdate', {
                detail: { personCount, hasBook, hasCellPhone }
            }));

            // if (personCount !== 1 || hasBook || hasCellPhone) {
            //     this.sendAlert(personCount, hasBook, hasCellPhone);
            // }
        } catch (error) {
            console.error("Detection error:", error);
        }

        if (this.video.readyState === 4) {
            requestAnimationFrame(() => this.detectObjects());
        }
    }

    startPeriodicScreenshots(intervalMs = this.screenshotIntervalTime) {
        if (this.isCapturingScreenshots) {
            console.log('Screenshot capture already running');
            return;
        }
    
        this.screenshotIntervalTime = intervalMs;
        this.isCapturingScreenshots = true;
        
        // Take initial screenshot
        this.captureScreenshot();
        
        // Set up interval for periodic screenshots
        this.screenshotInterval = setInterval(() => {
            this.captureScreenshot();
        }, this.screenshotIntervalTime);
    
        console.log(`Started periodic screenshots every ${intervalMs}ms`);
    }
    
    stopPeriodicScreenshots() {
        if (this.screenshotInterval) {
            clearInterval(this.screenshotInterval);
            this.screenshotInterval = null;
            this.isCapturingScreenshots = false;
            console.log('Stopped periodic screenshots');
        }
    }
    
    // async captureScreenshot() {
    //     if (!this.video || !this.video.videoWidth) {
    //         console.error('Video element not ready');
    //         return;
    //     }
    
    //     try {
    //         // Set canvas dimensions to match video
    //         this.screenshotCanvas.width = this.video.videoWidth;
    //         this.screenshotCanvas.height = this.video.videoHeight;
            
    //         // Draw current video frame to canvas
    //         this.screenshotContext.drawImage(this.video, 0, 0);
    
    //         // Convert to base64 image
    //         const screenshot = this.screenshotCanvas.toDataURL('image/jpeg', 0.8);
            
    //         // Add to queue instead of sending immediately
    //         await this.queueScreenshot({
    //             data: screenshot,
    //             timestamp: new Date().toISOString(),
    //             attempts: 0
    //         });
    
    //     } catch (error) {
    //         console.error('Error capturing screenshot:', error);
    //         this.screenshotStats.failed++;
    //         this.screenshotStats.lastError = {
    //             timestamp: new Date(),
    //             error: 'Capture error: ' + error.message
    //         };
    //     }
    // }

    async queueScreenshot(screenshotData) {
        // Remove oldest item if queue is full
        if (this.screenshotQueue.length >= this.maxQueueSize) {
            this.screenshotQueue.shift();
        }
        
        this.screenshotQueue.push(screenshotData);
        this.screenshotStats.attempted++;
        
        // Start processing queue if not already processing
        if (!this.isProcessingQueue) {
            await this.processScreenshotQueue();
        }
    }

    async processScreenshotQueue() {
        if (this.isProcessingQueue || this.screenshotQueue.length === 0) {
            return;
        }

        this.isProcessingQueue = true;

        while (this.screenshotQueue.length > 0) {
            const screenshot = this.screenshotQueue[0];
            let success = false;

            try {
                success = await this.saveScreenshot(screenshot);
                
                if (success) {
                    this.screenshotQueue.shift(); // Remove from queue
                    this.screenshotStats.successful++;
                    this.screenshotStats.lastSuccess = new Date();
                } else {
                    screenshot.attempts++;
                    
                    if (screenshot.attempts >= this.screenshotRetryAttempts) {
                        this.screenshotQueue.shift(); // Remove after max attempts
                        this.failedScreenshots.push({
                            timestamp: screenshot.timestamp,
                            error: 'Max retry attempts exceeded'
                        });
                    } else {
                        // Move to end of queue for retry
                        this.screenshotQueue.shift();
                        this.screenshotQueue.push(screenshot);
                        await this.delay(this.screenshotRetryDelay);
                    }
                }
            } catch (error) {
                console.error('Error processing screenshot:', error);
                screenshot.attempts++;
                this.screenshotStats.lastError = {
                    timestamp: new Date(),
                    error: error.message
                };
            }
        }

        this.isProcessingQueue = false;
    }

    async saveScreenshot(screenshot) {
        try {
            const response = await fetch('/api/screenshots', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    screenshot: screenshot.data,
                    timestamp: screenshot.timestamp,
                    retryAttempt: screenshot.attempts
                })
            });
    
            if (!response.ok) {
                const errorData = await response.json().catch(() => null);
                throw new Error(`Server returned ${response.status}${errorData ? ': ' + JSON.stringify(errorData) : ''}`);
            }
    
            const result = await response.json();
            console.log('Screenshot saved:', result);
            return true;
        } catch (error) {
            console.error('Error saving screenshot:', error);
            return false;
        }
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    getScreenshotStats() {
        return {
            ...this.screenshotStats,
            queueLength: this.screenshotQueue.length,
            failedScreenshots: this.failedScreenshots.length
        };
    }

    cleanup() {
        this.stopPeriodicScreenshots();
        
        // Try to process any remaining screenshots before cleanup
        if (this.screenshotQueue.length > 0) {
            console.log(`Attempting to process ${this.screenshotQueue.length} remaining screenshots...`);
            this.processScreenshotQueue().finally(() => {
                if (this.stream) {
                    this.stream.getTracks().forEach(track => track.stop());
                }
            });
        } else if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
        }
    }
}

// Initialize webcam manager when DOM is loaded
let webcamManager = null;

document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM fully loaded");
    webcamManager = new WebcamManager();
});

window.addEventListener('beforeunload', function() {
    if (webcamManager) {
        webcamManager.cleanup();
    }
});