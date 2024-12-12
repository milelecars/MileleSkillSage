class TestMonitoring {
  constructor(testId, candidateId) {
    var _a;
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
      this.flags = {};
      console.log("TestMonitoring initialized with:", {
        testId: this.testId,
        candidateId: this.candidateId,
        csrfToken: !!this.csrfToken
      });
      this.setupEventListeners();
      this.startPeriodicSync();
    } catch (error) {
      console.error("Error initializing TestMonitoring:", error);
    }
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
  setupEventListeners() {
    document.addEventListener("visibilitychange", () => {
      if (document.hidden) {
        this.metrics.tabSwitches++;
        window.monitoringData.metrics.tabSwitches = this.metrics.tabSwitches;
        this.checkIfFlagged();
        console.log("⚠️ Tab Switching Detected!", this.metrics.tabSwitches);
        this.updateDisplay();
        this.logSuspiciousBehavior("Tab Switches");
      }
    });
    window.addEventListener("blur", () => {
      this.metrics.windowBlurs++;
      window.monitoringData.metrics.windowBlurs = this.metrics.windowBlurs;
      this.checkIfFlagged();
      console.log("⚠️ Window focus lost!", this.metrics.windowBlurs);
      this.updateDisplay();
      this.logSuspiciousBehavior("Window Blurs");
    });
    document.addEventListener("mouseleave", () => {
      this.metrics.mouseExits++;
      window.monitoringData.metrics.mouseExits = this.metrics.mouseExits;
      this.checkIfFlagged();
      console.log("⚠️ Mouse exit detected!", this.metrics.mouseExits);
      this.updateDisplay();
      this.logSuspiciousBehavior("Mouse Exits");
    });
    document.addEventListener("copy", (e) => {
      e.preventDefault();
      this.metrics.copyCutAttempts++;
      window.monitoringData.metrics.copyCutAttempts = this.metrics.copyCutAttempts;
      this.checkIfFlagged();
      console.log("⚠️ Copying is not allowed!", this.metrics.copyCutAttempts);
      this.updateDisplay();
      this.logSuspiciousBehavior("Copy/Cut Attempts");
    });
    document.addEventListener("cut", (e) => {
      e.preventDefault();
      this.metrics.copyCutAttempts++;
      window.monitoringData.metrics.copyCutAttempts = this.metrics.copyCutAttempts;
      this.checkIfFlagged();
      console.log("⚠️ Cutting is not allowed!", this.metrics.copyCutAttempts);
      this.updateDisplay();
      this.logSuspiciousBehavior("Copy/Cut Attempts");
    });
    document.addEventListener("contextmenu", (e) => {
      e.preventDefault();
      this.metrics.rightClicks++;
      console.log("⚠️ Right clicking is not allowed!", this.metrics.rightClicks);
      this.updateDisplay();
      this.logSuspiciousBehavior("Right Clicks");
    });
    document.addEventListener("keydown", (e) => {
      if ((e.ctrlKey || e.metaKey) && ["c", "v", "x", "a", "p", "p"].includes(e.key.toLowerCase())) {
        e.preventDefault();
        this.metrics.keyboardShortcuts++;
        window.monitoringData.metrics.keyboardShortcuts = this.metrics.keyboardShortcuts;
        this.checkIfFlagged();
        console.log("⚠️ Keyboard shortcut detected!", e.key, this.metrics.keyboardShortcuts);
        this.updateDisplay();
        this.logSuspiciousBehavior("Keyboard Shortcuts");
      }
    });
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
    } else {
      console.log("Monitoring summary div not found");
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
        this.sendFlagData();
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
    this.metrics.warningCount++;
    const warningDiv = document.createElement("div");
    warningDiv.className = "bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded fixed top-5 right-5 z-50";
    warningDiv.role = "alert";
    warningDiv.innerHTML = message;
    document.body.appendChild(warningDiv);
    this.updateDisplay();
    setTimeout(() => {
      warningDiv.remove();
    }, 3e3);
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
