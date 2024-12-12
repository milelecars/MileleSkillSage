class WebcamManager {
  constructor() {
    var _a, _b;
    this.video = null;
    this.detectionStatus = null;
    this.model = null;
    this.stream = null;
    this.permissionGranted = false;
    this.deviceId = null;
    this.testId = ((_a = document.getElementById("test-id")) == null ? void 0 : _a.value) ?? null;
    this.candidateId = ((_b = document.getElementById("candidate-id")) == null ? void 0 : _b.value) ?? null;
    console.log("Test session data:", {
      test_id: this.testId,
      candidate_id: this.candidateId,
      elements: {
        testIdElement: !!document.getElementById("test-id"),
        candidateIdElement: !!document.getElementById("candidate-id")
      }
    });
    this.screenshotCanvas = document.createElement("canvas");
    this.screenshotContext = this.screenshotCanvas.getContext("2d");
    this.screenshotInterval = null;
    this.isCapturingScreenshots = false;
    this.screenshotIntervalTime = 3e4;
    this.screenshotQueue = [];
    this.maxQueueSize = 10;
    this.screenshotRetryAttempts = 3;
    this.screenshotRetryDelay = 5e3;
    this.isProcessingQueue = false;
    this.failedScreenshots = [];
    this.screenshotStats = {
      attempted: 0,
      successful: 0,
      failed: 0,
      lastSuccess: null,
      lastError: null
    };
    if (!this.testId || !this.candidateId) {
      console.log("No active test session, screenshots will be disabled");
      this.isCapturingScreenshots = false;
    }
    this.isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
    this.allowedRoutes = [
      "/invitation/\\w+",
      "/candidate/dashboard",
      "/tests/\\d+/",
      "/tests/\\d+/start"
    ];
    this.lastViolationTimes = {
      "More than One Person": 0,
      "Book": 0,
      "Cellphone": 0
    };
    this.violationFrameCounters = {
      "More than One Person": 0,
      "Book": 0,
      "Cellphone": 0
    };
    this.FRAME_THRESHOLD = 50;
    this.COOLDOWN_PERIOD = 3e4;
    this.initialize();
  }
  initializeCanvas() {
    try {
      this.screenshotCanvas = document.createElement("canvas");
      this.screenshotContext = this.screenshotCanvas.getContext("2d");
      if (!this.screenshotContext) {
        throw new Error("Failed to get 2D context from canvas");
      }
      this.screenshotCanvas.width = 640;
      this.screenshotCanvas.height = 480;
      console.log("Canvas initialized successfully");
    } catch (error) {
      console.error("Error initializing canvas:", error);
      this.screenshotCanvas = null;
      this.screenshotContext = null;
    }
  }
  async initializeCamera() {
    console.log("Initializing camera for route:", window.location.pathname);
    this.video = document.getElementById("video");
    this.detectionStatus = document.getElementById("detection-status");
    if (!this.video || !this.detectionStatus) {
      console.error("Required elements not found");
      return;
    }
    this.video.addEventListener("loadedmetadata", () => {
      console.log("Video metadata loaded, dimensions:", this.video.videoWidth, "x", this.video.videoHeight);
      if (this.screenshotCanvas && this.video.videoWidth) {
        this.screenshotCanvas.width = this.video.videoWidth;
        this.screenshotCanvas.height = this.video.videoHeight;
      }
    });
    await this.requestCameraAccess();
    if (this.screenshotCanvas && this.screenshotContext) {
      this.startPeriodicScreenshots(this.screenshotIntervalTime);
    } else {
      console.error("Cannot start screenshots - canvas not initialized");
    }
  }
  async initialize() {
    try {
      const permission = await this.checkServerPermission();
      this.permissionGranted = (permission == null ? void 0 : permission.granted) || false;
      this.deviceId = (permission == null ? void 0 : permission.deviceId) || null;
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
  updateStatus(personCount, hasBook, hasCellPhone) {
    let statusMessage = "";
    const now = Date.now();
    const handleViolation = (condition, type) => {
      if (condition) {
        this.violationFrameCounters[type]++;
        if (this.violationFrameCounters[type] >= this.FRAME_THRESHOLD) {
          const timeSinceLastViolation = now - this.lastViolationTimes[type];
          if (timeSinceLastViolation >= this.COOLDOWN_PERIOD) {
            document.dispatchEvent(new CustomEvent("webcamViolation", {
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
    if (personCount > 1) {
      statusMessage += `<p style='color: orange;'>${personCount} people detected!</p>`;
      handleViolation(true, "More than One Person");
    } else if (personCount === 0) {
      statusMessage += "<p style='color: red;'>No one is present!</p>";
      handleViolation(false, "More than One Person");
    } else {
      statusMessage += "<p style='color: green;'>One person detected</p>";
      handleViolation(false, "More than One Person");
    }
    if (hasBook) {
      statusMessage += "<p>Book detected</p>";
      handleViolation(true, "Book");
    } else {
      handleViolation(false, "Book");
    }
    if (hasCellPhone) {
      statusMessage += "<p>Cell phone detected</p>";
      handleViolation(true, "Cellphone");
    } else {
      handleViolation(false, "Cellphone");
    }
    if (this.detectionStatus) {
      this.detectionStatus.innerHTML = statusMessage;
    }
  }
  getCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute("content") : "";
  }
  async checkServerPermission() {
    try {
      console.log("Checking server permission...");
      const response = await fetch("/camera-permission", {
        method: "GET",
        headers: {
          "Accept": "application/json",
          "X-CSRF-TOKEN": this.getCsrfToken(),
          "X-Requested-With": "XMLHttpRequest"
        },
        credentials: "same-origin"
        // Important for cookies/session
      });
      console.log("Server response:", response);
      if (!response.ok) {
        throw new Error(`Server returned ${response.status}`);
      }
      const data = await response.json();
      console.log("Permission data:", data);
      return data.permission || { granted: false, deviceId: null, streamActive: false };
    } catch (error) {
      console.error("Error checking server permission:", error);
      return { granted: false, deviceId: null, streamActive: false };
    }
  }
  async updateServerPermission(granted, deviceId = null, streamActive = false) {
    try {
      console.log("Updating server permission...", { granted, deviceId, streamActive });
      const response = await fetch("/camera-permission", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "X-CSRF-TOKEN": this.getCsrfToken(),
          "X-Requested-With": "XMLHttpRequest"
        },
        credentials: "same-origin",
        // Important for cookies/session
        body: JSON.stringify({
          granted,
          deviceId,
          streamActive
        })
      });
      console.log("Update response:", response);
      if (!response.ok) {
        throw new Error(`Server returned ${response.status}`);
      }
      const data = await response.json();
      console.log("Update result:", data);
      return data.success;
    } catch (error) {
      console.error("Error updating server permission:", error);
      return false;
    }
  }
  shouldActivateCamera() {
    const currentPath = window.location.pathname;
    return this.allowedRoutes.some(
      (route) => new RegExp("^" + route).test(currentPath)
    );
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
        const cameraWarning = document.getElementById("camera-warning");
        if (cameraWarning) {
          cameraWarning.style.display = "none";
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
    console.log("4");
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
      predictions.forEach((prediction) => {
        if (prediction.class === "person") personCount++;
        if (prediction.class === "book") hasBook = true;
        if (prediction.class === "cell phone") hasCellPhone = true;
      });
      this.updateStatus(personCount, hasBook, hasCellPhone);
      document.dispatchEvent(new CustomEvent("webcamStatusUpdate", {
        detail: { personCount, hasBook, hasCellPhone }
      }));
    } catch (error) {
      console.error("Detection error:", error);
    }
    if (this.video.readyState === 4) {
      requestAnimationFrame(() => this.detectObjects());
    }
  }
  startPeriodicScreenshots(intervalMs = this.screenshotIntervalTime) {
    if (this.isCapturingScreenshots) {
      console.log("Screenshot capture already running");
      return;
    }
    if (!this.screenshotCanvas || !this.screenshotContext) {
      console.error("Cannot start screenshots - canvas not initialized");
      return;
    }
    this.screenshotIntervalTime = intervalMs;
    this.isCapturingScreenshots = true;
    console.log(`Starting periodic screenshots every ${intervalMs}ms`);
    this.captureScreenshot();
    this.screenshotInterval = setInterval(() => {
      this.captureScreenshot();
    }, this.screenshotIntervalTime);
  }
  stopPeriodicScreenshots() {
    if (this.screenshotInterval) {
      clearInterval(this.screenshotInterval);
      this.screenshotInterval = null;
      this.isCapturingScreenshots = false;
      console.log("Stopped periodic screenshots");
    }
  }
  async captureScreenshot() {
    var _a;
    if (!this.video || !this.video.videoWidth || !this.screenshotCanvas || !this.screenshotContext) {
      console.error("Video or canvas not ready", {
        video: !!this.video,
        videoWidth: (_a = this.video) == null ? void 0 : _a.videoWidth,
        canvas: !!this.screenshotCanvas,
        context: !!this.screenshotContext
      });
      return;
    }
    try {
      console.log("Attempting to capture screenshot...");
      this.screenshotCanvas.width = this.video.videoWidth;
      this.screenshotCanvas.height = this.video.videoHeight;
      this.screenshotContext.drawImage(this.video, 0, 0);
      const screenshot = this.screenshotCanvas.toDataURL("image/jpeg", 0.8);
      console.log("Sending screenshot to server...");
      const response = await fetch("/api/screenshots", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "X-CSRF-TOKEN": this.getCsrfToken(),
          "X-Requested-With": "XMLHttpRequest"
        },
        body: JSON.stringify({
          screenshot,
          timestamp: (/* @__PURE__ */ new Date()).toISOString()
        })
      });
      const data = await response.json();
      console.log("Server response:", data);
      if (!response.ok) {
        throw new Error(`Server returned ${response.status}: ${data.message || "Unknown error"}`);
      }
    } catch (error) {
      console.error("Error capturing/saving screenshot:", error);
    }
  }
  async queueScreenshot(screenshotData) {
    if (this.screenshotQueue.length >= this.maxQueueSize) {
      this.screenshotQueue.shift();
    }
    this.screenshotQueue.push(screenshotData);
    this.screenshotStats.attempted++;
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
          this.screenshotQueue.shift();
          this.screenshotStats.successful++;
          this.screenshotStats.lastSuccess = /* @__PURE__ */ new Date();
        } else {
          screenshot.attempts++;
          if (screenshot.attempts >= this.screenshotRetryAttempts) {
            this.screenshotQueue.shift();
            this.failedScreenshots.push({
              timestamp: screenshot.timestamp,
              error: "Max retry attempts exceeded"
            });
          } else {
            this.screenshotQueue.shift();
            this.screenshotQueue.push(screenshot);
            await this.delay(this.screenshotRetryDelay);
          }
        }
      } catch (error) {
        console.error("Error processing screenshot:", error);
        screenshot.attempts++;
        this.screenshotStats.lastError = {
          timestamp: /* @__PURE__ */ new Date(),
          error: error.message
        };
      }
    }
    this.isProcessingQueue = false;
  }
  delay(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
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
    if (this.screenshotQueue.length > 0) {
      console.log(`Attempting to process ${this.screenshotQueue.length} remaining screenshots...`);
      this.processScreenshotQueue().finally(() => {
        if (this.stream) {
          this.stream.getTracks().forEach((track) => track.stop());
        }
      });
    } else if (this.stream) {
      this.stream.getTracks().forEach((track) => track.stop());
    }
  }
}
let webcamManager = null;
document.addEventListener("DOMContentLoaded", function() {
  const videoElement = document.getElementById("video");
  const statusElement = document.getElementById("detection-status");
  if (videoElement && statusElement) {
    console.log("Required webcam elements found, initializing WebcamManager");
    webcamManager = new WebcamManager();
  } else {
    console.log("Webcam elements not found on this page, skipping initialization");
  }
});
window.addEventListener("beforeunload", function() {
  if (webcamManager) {
    webcamManager.cleanup();
  }
});
