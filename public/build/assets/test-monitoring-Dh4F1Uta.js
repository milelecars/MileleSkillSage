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
      this.metrics = {
        tabSwitches: 0,
        windowBlurs: 0,
        mouseExits: 0,
        copyCutAttempts: 0,
        rightClicks: 0,
        keyboardShortcuts: 0,
        warningCount: 0
      };
      window.monitoringData.metrics = { ...this.metrics };
      this.flags = {};
      console.log("TestMonitoring initialized with:", {
        testId: this.testId,
        candidateId: this.candidateId,
        csrfToken: !!this.csrfToken
      });
      this.setupEventListeners();
      this.startPeriodicSync();
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
  // Helper method to safely update metrics
  updateMetric(metricName, value) {
    this.metrics[metricName] = value;
    window.monitoringData.metrics[metricName] = value;
    this.checkIfFlagged();
    this.updateDisplay();
  }
  // Helper method to handle violations
  handleViolation(event, metricName, message) {
    if (event) {
      event.preventDefault();
    }
    this.updateMetric(metricName, this.metrics[metricName] + 1);
    console.log(`⚠️ ${message}`, this.metrics[metricName]);
    this.logSuspiciousBehavior(metricName);
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
  async logSuspiciousBehavior(flagType) {
    try {
      if (!this.csrfToken || !this.testId) {
        console.warn("Missing required data for logging:", {
          csrfToken: !!this.csrfToken,
          testId: this.testId
        });
        return;
      }
      const response = await fetch("/flag", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": this.csrfToken
        },
        body: JSON.stringify({
          flag_type: flagType,
          test_session_id: this.testId
        })
      });
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
    } catch (error) {
      console.warn("Error logging behavior:", error);
    }
  }
  updateDisplay() {
    const summaryDiv = document.querySelector(".monitoring-summary");
    if (summaryDiv) {
      Object.entries(this.metrics).forEach(([key, value]) => {
        const element = document.querySelector(`[data-metric="${key}"]`);
        if (element) {
          element.textContent = value;
          const threshold = this.getThreshold(key);
          element.className = value > threshold ? "text-red-600" : "text-gray-600";
        }
        const flagElement = document.querySelector(`[data-metric-flag="${key}"]`);
        if (flagElement) {
          const isThisMetricFlagged = value > this.getThreshold(key);
          this.flags[key] = isThisMetricFlagged;
          flagElement.textContent = isThisMetricFlagged ? "Yes" : "No";
          flagElement.className = isThisMetricFlagged ? "text-red-600" : "text-green-600";
        }
      });
    }
  }
  checkIfFlagged() {
    const thresholds = this.getThresholds();
    let anyFlagged = false;
    for (const [metric, threshold] of Object.entries(thresholds)) {
      const isThisMetricFlagged = this.metrics[metric] > threshold;
      this.flags[metric] = isThisMetricFlagged;
      if (isThisMetricFlagged) {
        anyFlagged = true;
        this.logSuspiciousBehavior(metric);
      }
    }
    return anyFlagged;
  }
  getThreshold(metric) {
    return this.getThresholds()[metric] || 0;
  }
  getThresholds() {
    return {
      tabSwitches: 3,
      windowBlurs: 5,
      mouseExits: 5,
      copyCutAttempts: 2,
      rightClicks: 3,
      keyboardShortcuts: 3,
      warningCount: 3
    };
  }
  startPeriodicSync() {
    setInterval(() => {
      this.updateDisplay();
    }, 1e3);
  }
  showWarning(message) {
    this.updateMetric("warningCount", this.metrics.warningCount + 1);
    const warningDiv = document.createElement("div");
    warningDiv.className = "bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded fixed top-5 right-5 z-50";
    warningDiv.role = "alert";
    warningDiv.innerHTML = message;
    document.body.appendChild(warningDiv);
    setTimeout(() => {
      warningDiv.remove();
    }, 3e3);
  }
}
if (!window.monitoringData) {
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
