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

        // Initialize camera only after checking permissions
        this.initialize();
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

    updateStatus(personCount, hasBook, hasCellPhone) {
        let statusMessage = '';

        if (personCount === 0) {
            statusMessage = "<p style='color: red;'>No person detected!</p>";
        } else if (personCount === 1) {
            statusMessage = "<p style='color: green;'>One person detected.</p>";
        } else {
            statusMessage = `<p style='color: orange;'>${personCount} people detected!</p>`;
        }

        if (hasBook) {
            statusMessage += "<p>Book detected.</p>";
        }

        if (hasCellPhone) {
            statusMessage += "<p>Cell phone detected.</p>";
        }

        if (this.detectionStatus) {
            this.detectionStatus.innerHTML = statusMessage;
        }
    }

    handleCameraError(error) {
        console.log("7")
        console.error("Camera error:", error);
        if (this.detectionStatus) {
            this.detectionStatus.innerHTML = "<p style='color: red;'>Camera Error: " + error + "</p>";
        }
        
        const cameraWarning = document.getElementById('camera-warning');
        if (cameraWarning) {
            cameraWarning.style.display = 'flex';
        }

        document.dispatchEvent(new CustomEvent('webcamStatusUpdate', {
            detail: {
                personCount: 0,
                hasBook: false,
                hasCellPhone: false
            }
        }));
    }

    // sendAlert(personCount, hasBook, hasCellPhone) {
    //     console.log("8")
    //     const alertData = {
    //         personCount: personCount,
    //         hasBook: hasBook,
    //         hasCellPhone: hasCellPhone,
    //         timestamp: new Date().toISOString()
    //     };

    //     fetch('/flag', {
    //         method: 'POST',
    //         headers: {
    //             'Content-Type': 'application/json',
    //             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    //         },
    //         body: JSON.stringify(alertData)
    //     })
    //     .then(response => response.json())
    //     .then(data => console.log('Alert sent:', data))
    //     .catch(error => console.error('Error sending alert:', error));
    // }

    cleanup() {
        if (this.isSafari) {
            this.preserveStream().catch(console.error);
        } else {
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
                this.stream = null;
            }
        }
        
        if (this.video) {
            this.video.srcObject = null;
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