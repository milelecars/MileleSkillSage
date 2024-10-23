console.log("Webcam script loaded");

document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM fully loaded");
    if (document.getElementById('dashboard-container')) {
        console.log("Dashboard page detected. Initializing webcam...");
        initializeWebcam();
    } else {
        console.log("Not on dashboard page. Webcam not initialized.");
    }
});

function initializeWebcam() {
    let video;
    let detectionStatus;
    let model;

    async function startWebcam() {
        video = document.getElementById('video');
        detectionStatus = document.getElementById('detection-status');
        if (!video || !detectionStatus) {
            console.error("Required elements not found. video:", video, "detectionStatus:", detectionStatus);
            return;
        }
        try {
            console.log("Requesting camera permission...");
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            video.srcObject = stream;
            video.onloadedmetadata = () => {
                video.play();
            };
            detectionStatus.innerHTML = "<p style='color: green;'>Webcam started successfully</p>";
            console.log("Webcam started");

            
            console.log("Loading COCO-SSD model...");
            model = await cocoSsd.load();
            console.log("COCO-SSD model loaded successfully");

            
            detectObjects();
        } catch (error) {
            console.error("Error in startWebcam:", error);
            if (detectionStatus) {
                detectionStatus.innerHTML = "<p style='color: red;'>Error: " + error.message + "</p>";
            }
        }
    }

    async function detectObjects() {
        if (!model) {
            console.error("Model not loaded yet");
            return;
        }

        try {
            const predictions = await model.detect(video);
            
            let personCount = 0;
            let hasBook = false;
            let hasCellPhone = false;

            predictions.forEach(prediction => {
                if (prediction.class === 'person') personCount++;
                if (prediction.class === 'book') hasBook = true;
                if (prediction.class === 'cell phone') hasCellPhone = true;
            });

            updateStatus(personCount, hasBook, hasCellPhone);

            
            const event = new CustomEvent('webcamStatusUpdate', {
                detail: { personCount, hasBook, hasCellPhone }
            });
            document.dispatchEvent(event);

            
            if (personCount !== 1 || hasBook || hasCellPhone) {
                sendAlert(personCount, hasBook, hasCellPhone);
            }
        } catch (error) {
            console.error("Error during object detection:", error);
        }

        
        requestAnimationFrame(detectObjects);
    }

    function updateStatus(personCount, hasBook, hasCellPhone) {
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

        if (detectionStatus) {
            detectionStatus.innerHTML = statusMessage;
        }
    }

    function sendAlert(personCount, hasBook, hasCellPhone) {
        const alertData = {
            personCount: personCount,
            hasBook: hasBook,
            hasCellPhone: hasCellPhone,
            timestamp: new Date().toISOString()
        };

        fetch('/flag', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(alertData)
        })
        .then(response => response.json())
        .then(data => console.log('Alert sent:', data))
        .catch(error => console.error('Error sending alert:', error));
    }

    
    startWebcam();
}