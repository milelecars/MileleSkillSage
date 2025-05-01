class WebcamManager {
  constructor() {
    var _a, _b, _c;
    this.video = null;
    this.detectionStatus = null;
    this.model = null;
    this.stream = null;
    this.permissionGranted = false;
    this.deviceId = null;
    this.testId = ((_a = document.getElementById("test-id")) == null ? void 0 : _a.value) ?? null;
    this.candidateId = ((_b = document.getElementById("candidate-id")) == null ? void 0 : _b.value) ?? null;
    this.csrfToken = (_c = document.querySelector('meta[name="csrf-token"]')) == null ? void 0 : _c.getAttribute("content");
    this.specificPageRegex = /^\/tests\/\d+\/setup$/;
    this.violationThreshold = 30;
    this.violationThresholdCameraOff = 30;
    this.violationCounts = {
      multiplePeople: 0,
      cameraTurnedOff: 0
    };
    console.log("Test session data:", {
      test_id: this.testId,
      candidate_id: this.candidateId,
      elements: {
        testIdElement: document.getElementById("test-id"),
        candidateIdElement: document.getElementById("candidate-id")
      }
    });
    this.screenshotCanvas = document.createElement("canvas");
    this.screenshotContext = this.screenshotCanvas.getContext("2d");
    this.screenshotInterval = null;
    this.isCapturingScreenshots = false;
    this.screenshotIntervalTime = 6e4;
    this.screenshotQueue = [];
    this.maxQueueSize = 10;
    this.screenshotRetryAttempts = 3;
    this.screenshotRetryDelay = 1e4;
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
    this.detectionThrottleDelay = 150;
    this.lastDetectionTime = 0;
    this.elemCache = {};
    this.statusTemplates = {
      multiplePeople: (count) => `<p style='color: orange;'>${count} people detected! (Count: ${this.violationCounts.multiplePeople})</p>`,
      cameraTurnedOff: (count) => `<p style='color: red;'>Camera turned off! (Count: ${this.violationCounts.cameraTurnedOff})</p>`,
      onePerson: `<p style='color: green;'>One person detected</p>`,
      noPerson: `<p style='color: red;'>No one is present!</p>`,
      bookDetected: `<p style='color: orange;'>Book detected!</p>`,
      cellPhoneDetected: `<p style='color: orange;'>Cell phone detected!</p>`
    };
    this.initialize();
  }
  // Performance optimization: Cache DOM elements
  getElement(id) {
    if (!this.elemCache[id]) {
      this.elemCache[id] = document.getElementById(id);
    }
    return this.elemCache[id];
  }
  // No person detected
  isSpecificPage() {
    return this.specificPageRegex.test(window.location.pathname);
  }
  showNoPersonPopup() {
    if (!this.isSpecificPage()) {
      return;
    }
    if (!document.getElementById("no-person-overlay")) {
      const overlay = document.createElement("div");
      overlay.id = "no-person-overlay";
      overlay.style.position = "fixed";
      overlay.style.top = "0";
      overlay.style.left = "0";
      overlay.style.width = "100%";
      overlay.style.height = "100%";
      overlay.style.backdropFilter = "blur(10px)";
      overlay.style.zIndex = "999";
      document.body.appendChild(overlay);
      const popup = document.createElement("div");
      popup.id = "no-person-popup";
      popup.style.position = "fixed";
      popup.style.top = "50%";
      popup.style.left = "50%";
      popup.style.transform = "translate(-50%, -50%)";
      popup.style.backgroundColor = "white";
      popup.style.color = "black";
      popup.style.padding = "20px";
      popup.style.borderRadius = "10px";
      popup.style.zIndex = "1000";
      popup.style.textAlign = "center";
      popup.style.boxShadow = "0 2px 2px rgba(0, 0, 0, 0.1)";
      popup.innerText = "No person detected!";
      document.body.appendChild(popup);
    }
  }
  hideNoPersonPopup() {
    if (!this.isSpecificPage()) {
      return;
    }
    const popup = document.getElementById("no-person-popup");
    const overlay = document.getElementById("no-person-overlay");
    if (popup) {
      popup.remove();
    }
    if (overlay) {
      overlay.remove();
    }
  }
  cleanup() {
    this.stopPeriodicScreenshots();
    this.hideNoPersonPopup();
    if (this.detectionRAF) {
      cancelAnimationFrame(this.detectionRAF);
      this.detectionRAF = null;
    }
    if (window.__PRESERVE_STREAM__) {
      console.log("Preserving stream – skipping track stop and permission clear.");
      return;
    }
    const stopStream = () => {
      if (this.stream) {
        this.stream.getTracks().forEach((track) => track.stop());
      }
      window.__ACTIVE_STREAM__ = null;
    };
    if (this.screenshotQueue.length > 0) {
      console.log(`Processing ${this.screenshotQueue.length} queued screenshots before cleanup...`);
      this.processScreenshotQueue().finally(stopStream);
    } else {
      stopStream();
    }
  }
  async initialize() {
    try {
      this.hideNoPersonPopup();
      const permission = await this.checkServerPermission();
      console.log("Device ID (Laravel):", permission.deviceId);
      console.log("Permission granted:", permission.granted);
      console.log("Stream active:", permission.streamActive);
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
  // Suspension
  updateStatus(personCount, hasBook, hasCellPhone) {
    let statusParts = [];
    if (personCount > 1) {
      this.violationCounts.multiplePeople++;
      statusParts.push(this.statusTemplates.multiplePeople(personCount));
      if (this.violationCounts.multiplePeople >= this.violationThreshold) {
        this.suspendTest("multiplePeople");
      }
    } else if (personCount === 0) {
      statusParts.push(this.statusTemplates.noPerson);
      this.showNoPersonPopup();
    } else {
      statusParts.push(this.statusTemplates.onePerson);
      this.hideNoPersonPopup();
      this.violationCounts.multiplePeople = 0;
    }
    if (!this.stream || !this.stream.active) {
      this.violationCounts.cameraTurnedOff++;
      statusParts.push(this.statusTemplates.cameraTurnedOff(this.violationCounts.cameraTurnedOff));
      if (this.violationCounts.cameraTurnedOff >= this.violationThresholdCameraOff) {
        this.suspendTest("cameraTurnedOff");
      }
    } else {
      this.violationCounts.cameraTurnedOff = 0;
    }
    if (hasBook) {
      statusParts.push(this.statusTemplates.bookDetected);
      this.handleViolation("bookDetected", "Book detected!");
    }
    if (hasCellPhone) {
      statusParts.push(this.statusTemplates.cellPhoneDetected);
      this.handleViolation("cellPhoneDetected", "Cell phone detected!");
    }
    if (this.detectionStatus && statusParts.length > 0) {
      this.detectionStatus.innerHTML = statusParts.join("");
    }
  }
  handleViolation(metricName, message) {
    this.violationCounts[metricName] = (this.violationCounts[metricName] || 0) + 1;
    const currentCount = this.violationCounts[metricName];
    console.log(`⚠️ ${message} (Count: ${currentCount})`);
    this.updateViolationLog(`${message} (${currentCount})`);
    if (this.violationCounts[metricName] >= this.violationThreshold) {
      this.suspendTest(metricName);
    }
  }
  updateViolationLog(message) {
    const logDiv = document.getElementById("violation-log");
    if (logDiv) {
      logDiv.textContent = message;
    }
  }
  async suspendTest(violationType) {
    console.log(`Test suspended due to excessive ${violationType}`);
    try {
      await this.logSuspension(violationType);
      const response = await fetch("/get-unsuspend-count", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": this.csrfToken
        },
        body: JSON.stringify({
          testId: this.testId,
          candidateId: this.candidateId
        })
      });
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const data = await response.json();
      if (data.unsuspend_count == 1) {
        window.location.href = "/candidate/dashboard";
        return;
      } else {
        window.location.href = `/tests/${this.testId}/suspended?reason=${violationType}`;
      }
    } catch (error) {
      console.error("Error during suspension:", error);
    }
  }
  async logSuspension(violationType) {
    try {
      console.log("Sending suspension data to backend:", {
        testId: this.testId,
        candidateId: this.candidateId,
        violationType
      });
      const response = await fetch("/log-suspension", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": this.csrfToken
        },
        body: JSON.stringify({
          testId: this.testId,
          candidateId: this.candidateId,
          violationType
        })
      });
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      console.log("Suspension logged successfully");
    } catch (error) {
      console.error("Error logging suspension:", error);
    }
  }
  initializeCanvas() {
    try {
      this.screenshotCanvas = document.createElement("canvas");
      this.screenshotContext = this.screenshotCanvas.getContext("2d", {
        willReadFrequently: true
        // Performance optimization for frequent pixel manipulations
      });
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
    if (window.__ACTIVE_STREAM__ && window.__ACTIVE_STREAM__.active) {
      this.stream = window.__ACTIVE_STREAM__;
      this.video.srcObject = this.stream;
      this.video.play();
      this.video.onloadeddata = () => {
        this.video.play();
        this.initializeDetection();
      };
    } else {
      await this.requestCameraAccess();
    }
    if (this.screenshotCanvas && this.screenshotContext) {
      this.startPeriodicScreenshots(this.screenshotIntervalTime);
    } else {
      console.error("Cannot start screenshots - canvas not initialized");
    }
    this.cameraInitialized = true;
  }
  getCsrfToken() {
    if (!this._csrfToken) {
      const token = document.querySelector('meta[name="csrf-token"]');
      this._csrfToken = token ? token.getAttribute("content") : "";
    }
    return this._csrfToken;
  }
  async checkServerPermission() {
    var _a, _b, _c;
    try {
      console.log("Checking server permission...");
      const url = `/camera-permission?testId=${this.testId}&candidateId=${this.candidateId}`;
      const response = await fetch(url, {
        method: "GET",
        headers: {
          "Accept": "application/json",
          "X-CSRF-TOKEN": this.getCsrfToken(),
          "X-Requested-With": "XMLHttpRequest"
        },
        credentials: "same-origin"
      });
      const data = await response.json();
      console.log("Permission data (Laravel):", data.permission);
      console.log("Device ID (Laravel):", (_a = data.permission) == null ? void 0 : _a.deviceId);
      console.log("Permission granted:", (_b = data.permission) == null ? void 0 : _b.granted);
      console.log("Stream active:", (_c = data.permission) == null ? void 0 : _c.streamActive);
      return data.permission || { granted: false, deviceId: null, streamActive: false };
    } catch (error) {
      console.error("Error checking server permission:", error);
      return { granted: false, deviceId: null, streamActive: false };
    }
  }
  async updateServerPermission(granted, deviceId = null, streamActive = false) {
    try {
      console.log("Updating server permission...", { granted, deviceId, streamActive });
      console.log("testId:", this.testId, "candidateId:", this.candidateId);
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
          streamActive,
          testId: this.testId,
          candidateId: this.candidateId
        })
      });
      console.log("Update response:", response);
      if (!response.ok) {
        throw new Error(`Server returned ${response.status}`);
      }
      const data = await response.json();
      console.log("Update result:", data);
      if (data.success && data.permission) {
        sessionStorage.setItem("camera_permission_granted", data.permission.granted ? "yes" : "no");
        sessionStorage.setItem("camera_device_id", data.permission.deviceId || "");
        localStorage.setItem("camera_permission_granted", data.permission.granted ? "yes" : "no");
        localStorage.setItem("camera_device_id", data.permission.deviceId || "");
      }
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
    if (window.__ACTIVE_STREAM__ && window.__ACTIVE_STREAM__.active) {
      console.log("Reusing active stream from global object");
      this.stream = window.__ACTIVE_STREAM__;
      this.video.srcObject = this.stream;
      this.video.onloadeddata = () => {
        this.video.play();
        this.initializeDetection();
      };
      return;
    }
    try {
      if (this.deviceId && !this.isSafari) {
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
      window.__ACTIVE_STREAM__ = this.stream;
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
    this.video.srcObject = this.stream;
    window.__ACTIVE_STREAM__ = this.stream;
    const videoTrack = this.stream.getVideoTracks()[0];
    if (videoTrack) {
      const settings = videoTrack.getSettings();
      await this.updateServerPermission(true, settings.deviceId, true);
    }
    this.video.srcObject = this.stream;
    return new Promise((resolve) => {
      this.video.onloadedmetadata = () => {
        console.log("Video metadata loaded");
        this.video.onloadeddata = () => {
          this.video.play();
          this.initializeDetection();
        };
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
          window.__ACTIVE_STREAM__ = this.stream;
          return true;
        } catch (error) {
          console.error("Failed to preserve stream:", error);
          return false;
        }
      }
    }
    return false;
  }
  handleCameraError(message) {
    console.error("Camera error:", message);
    if (this.detectionStatus) {
      this.detectionStatus.innerHTML = `<p style='color: red;'>Camera error: ${message}</p>`;
    }
  }
  async initializeDetection() {
    try {
      console.log("Loading COCO-SSD model...");
      this.model = await cocoSsd.load();
      console.log("COCO-SSD model loaded successfully");
      if (!this.video || this.video.readyState < 2) {
        console.warn("Video not ready for detection");
        if (this.video.readyState >= 2) {
          this.detectObjects();
        } else {
          this.video.addEventListener("loadeddata", () => this.detectObjects(), { once: true });
        }
        return;
      }
    } catch (error) {
      console.error("Model initialization error:", error);
      this.detectionStatus.innerHTML += "<p style='color: red;'>Object detection unavailable</p>";
    }
  }
  async detectObjects() {
    if (!this.model || !this.video || !this.shouldActivateCamera()) {
      return;
    }
    const now = performance.now();
    if (now - this.lastDetectionTime < this.detectionThrottleDelay) {
      this.detectionRAF = requestAnimationFrame(() => this.detectObjects());
      return;
    }
    this.lastDetectionTime = now;
    try {
      const predictions = await this.model.detect(this.video);
      let personCount = 0;
      let hasBook = false;
      let hasCellPhone = false;
      const len = predictions.length;
      for (let i = 0; i < len; i++) {
        const predClass = predictions[i].class;
        if (predClass === "person") personCount++;
        else if (predClass === "book") hasBook = true;
        else if (predClass === "cell phone") hasCellPhone = true;
      }
      this.updateStatus(personCount, hasBook, hasCellPhone);
      document.dispatchEvent(new CustomEvent("webcamStatusUpdate", {
        detail: { personCount, hasBook, hasCellPhone }
      }));
    } catch (error) {
      console.error("Detection error:", error);
    }
    if (this.video.readyState === 4) {
      this.detectionRAF = requestAnimationFrame(() => this.detectObjects());
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
      if (this.screenshotCanvas.width !== this.video.videoWidth || this.screenshotCanvas.height !== this.video.videoHeight) {
        this.screenshotCanvas.width = this.video.videoWidth;
        this.screenshotCanvas.height = this.video.videoHeight;
      }
      this.screenshotContext.drawImage(this.video, 0, 0);
      const screenshot = this.screenshotCanvas.toDataURL("image/jpeg", 0.7);
      fetch("/api/screenshots", {
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
      }).then((response) => response.json()).then((data) => console.log("Server response:", data)).catch((error) => console.error("Error sending screenshot:", error));
    } catch (error) {
      console.error("Error capturing screenshot:", error);
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
}
window.webcamManager = null;
document.addEventListener("DOMContentLoaded", function() {
  const videoElement = document.getElementById("video");
  const statusElement = document.getElementById("detection-status");
  if (videoElement && statusElement && !window.webcamManager) {
    console.log("Initializing WebcamManager...");
    window.webcamManager = new WebcamManager();
  } else {
    console.log("Webcam elements not found on this page, skipping initialization");
  }
}, { passive: true });
window.addEventListener("beforeunload", function() {
  if (webcamManager && !window.__PRESERVE_STREAM__) {
    webcamManager.cleanup();
  }
});
