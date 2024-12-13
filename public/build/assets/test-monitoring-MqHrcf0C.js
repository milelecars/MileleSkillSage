class TestMonitoring {
  constructor(testId, candidateId) {
    var _a;
    if (window.testMonitoring) {
      console.log("TestMonitoring already initialized, returning existing instance");
      return window.testMonitoring;
    }
    window.testMonitoring = this;
    try {
      this.testId = testId;
      this.candidateId = candidateId;
      this.csrfToken = (_a = document.querySelector('meta[name="csrf-token"]')) == null ? void 0 : _a.getAttribute("content");
      this.metrics = {};
      console.log("TestMonitoring initialized with:", {
        testId: this.testId,
        candidateId: this.candidateId,
        csrfToken: !!this.csrfToken
      });
      this.setupEventListeners();
      this.preventUserSelection();
    } catch (error) {
      console.error("Error initializing TestMonitoring:", error);
    }
  }
  preventUserSelection() {
    document.body.style.userSelect = "none";
    document.body.style.webkitUserSelect = "none";
    document.body.style.msUserSelect = "none";
    document.body.style.mozUserSelect = "none";
    document.querySelectorAll("input, textarea").forEach((element) => {
      element.style.userSelect = "text";
      element.style.webkitUserSelect = "text";
      element.style.msUserSelect = "text";
      element.style.mozUserSelect = "text";
    });
  }
  handleViolation(event, metricName, message) {
    if (event) {
      event.preventDefault();
    }
    this.metrics[metricName] = (this.metrics[metricName] || 0) + 1;
    const currentCount = this.metrics[metricName];
    console.log(`⚠️ ${message} (Count: ${currentCount})`);
    this.updateViolationLog(`${message} (${currentCount})`);
    const flagType = this.getFlagTypeFromMetric(metricName);
    this.logSuspiciousBehavior(flagType, currentCount);
  }
  updateViolationLog(message) {
    const logDiv = document.getElementById("violation-log");
    if (logDiv) {
      logDiv.textContent = message;
    }
  }
  getFlagTypeFromMetric(metricName) {
    const withSpaces = metricName.replace(/([A-Z])/g, " $1");
    return withSpaces.charAt(0).toUpperCase() + withSpaces.slice(1).toLowerCase().trim();
  }
  setupEventListeners() {
    document.addEventListener("visibilitychange", (e) => {
      if (document.hidden) {
        this.handleViolation(e, "tabSwitches", "Tab Switching Detected!");
      }
    });
    window.addEventListener("blur", (e) => {
      this.handleViolation(e, "windowBlurs", "Window focus lost!");
    });
    document.addEventListener("mouseleave", (e) => {
      this.handleViolation(e, "mouseExits", "Mouse exit detected!");
    });
    ["copy", "cut"].forEach((eventType) => {
      document.addEventListener(eventType, (e) => {
        this.handleViolation(e, "copyCutAttempts", `${eventType} is not allowed!`);
      });
    });
    document.addEventListener("contextmenu", (e) => {
      this.handleViolation(e, "rightClicks", "Right clicking is not allowed!");
    });
    document.addEventListener("keydown", (e) => {
      if ((e.ctrlKey || e.metaKey) && ["c", "v", "x", "a", "p", "f12"].includes(e.key.toLowerCase())) {
        this.handleViolation(e, "keyboardShortcuts", `Keyboard shortcut detected: ${e.key}`);
      }
      if (e.key === "F12" || (e.ctrlKey || e.metaKey) && e.shiftKey && (e.key === "I" || e.key === "C")) {
        this.handleViolation(e, "keyboardShortcuts", "Developer Tools shortcut detected!");
      }
    });
  }
  async logSuspiciousBehavior(flagType, occurrences) {
    try {
      const response = await fetch("/flag", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": this.csrfToken,
          "Accept": "application/json"
        },
        body: JSON.stringify({
          candidateId: this.candidateId,
          testId: this.testId,
          flagType,
          occurrences,
          isFlagged: true
        })
      });
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const data = await response.json();
      console.log("Violation logged to backend:", data);
    } catch (error) {
      console.error("Error logging violation:", error);
    }
  }
}
document.addEventListener("DOMContentLoaded", () => {
  try {
    const testIdElement = document.getElementById("test-id");
    const candidateIdElement = document.getElementById("candidate-id");
    if (testIdElement && candidateIdElement) {
      new TestMonitoring(
        testIdElement.value,
        candidateIdElement.value
      );
    } else {
      console.warn("Test monitoring not initialized - required elements not found", {
        testIdFound: !!testIdElement,
        candidateIdFound: !!candidateIdElement
      });
    }
  } catch (error) {
    console.error("Error during TestMonitoring initialization:", error);
  }
});
window.TestMonitoring = TestMonitoring;
