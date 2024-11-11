class TestMonitoring {
    constructor() {
        console.log('TestMonitoring initialized')
        this.metrics = {
            tabSwitches: 0,
            windowBlurs: 0,
            warningCount: 0,
            mouseExits: 0,
            copyCutAttempts: 0,
            rightClicks: 0,
            keyboardShortcuts: 0
        };

        this.flags = {
            tabSwitches: false,
            windowBlurs: false,
            warningCount: false,
            mouseExits: false,
            copyCutAttempts: false,
            rightClicks: false,
            keyboardShortcuts: false
        };

        window.monitoringData = {
            metrics: this.metrics,
            flags: this.flags
        };

        this.setupEventListeners();
        this.startPeriodicSync();
    }

    setupEventListeners() {
        console.log('Setting up event listeners');

        // Tab Switching
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.metrics.tabSwitches++;
                window.monitoringData.metrics.tabSwitches = this.metrics.tabSwitches;
                this.checkIfFlagged();
                this.showWarning("Tab Switching Detected!");
                this.updateDisplay();
            }
        });

        // Window Blur 
        window.addEventListener('blur', () => {
            this.metrics.windowBlurs++;
            window.monitoringData.metrics.windowBlurs = this.metrics.windowBlurs;
            this.checkIfFlagged();
            this.showWarning("Window focus lost!");
            this.updateDisplay();
        });

        // Mouse Leaving the window 
        document.addEventListener('mouseleave', () => {
            this.metrics.mouseExits++;
            window.monitoringData.metrics.mouseExits = this.metrics.mouseExits;
            this.checkIfFlagged();
            this.showWarning("Mouse exit detected!");
            this.updateDisplay();
        });

        // Detect copy/cut attempts
        document.addEventListener('copy', (e) => {
            e.preventDefault();
            this.metrics.copyCutAttempts++;
            window.monitoringData.metrics.copyCutAttempts = this.metrics.copyCutAttempts;
            this.checkIfFlagged();
            this.showWarning('Copying is not allowed!');
            this.updateDisplay();
        });

        document.addEventListener('cut', (e) => {
            e.preventDefault();
            this.metrics.copyCutAttempts++;
            window.monitoringData.metrics.copyCutAttempts = this.metrics.copyCutAttempts;
            this.checkIfFlagged();
            this.showWarning('Cutting is not allowed!');
            this.updateDisplay();
        });

        // Rigth Click Attempt 
        document.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            this.metrics.rightClicks++;
            console.log('Right click detected:', this.metrics.rightClicks); // Debug log
            this.showWarning('Right clicking is not allowed!');
            this.updateDisplay();
        });

        // Detect keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && 
                ['c', 'v', 'x', 'a', 'p', 'p'].includes(e.key.toLowerCase())) {
                e.preventDefault();
                this.metrics.keyboardShortcuts++;
                window.monitoringData.metrics.keyboardShortcuts = this.metrics.keyboardShortcuts;
                this.checkIfFlagged();
                this.showWarning('Keyboard shortcuts are not allowed!');
                this.updateDisplay();
            }
        });
    }

    updateDisplay() {
        console.log('Updating display with metrics:', this.metrics);
        const summaryDiv = document.querySelector('.monitoring-summary');
        if (summaryDiv) {
            // Update metrics
            Object.entries(this.metrics).forEach(([key, value]) => {
                const element = document.querySelector(`[data-metric="${key}"]`);
                if (element) {
                    element.textContent = value;
                    const threshold = this.getThreshold(key);
                    element.className = value > threshold ? 'text-red-600' : 'text-gray-600';
                }

                // Update individual flags
                const flagElement = document.querySelector(`[data-metric-flag="${key}"]`);
                if (flagElement) {
                    const isThisMetricFlagged = value > this.getThreshold(key);
                    this.flags[key] = isThisMetricFlagged;
                    flagElement.textContent = isThisMetricFlagged ? 'Yes' : 'No';
                    flagElement.className = isThisMetricFlagged ? 'text-red-600' : 'text-green-600';
                }
            });
        } else {
            console.log('Monitoring summary div not found');
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
        }, 1000);
    }

    showWarning(message) {
        this.metrics.warningCount++;
        const warningDiv = document.createElement('div');
        warningDiv.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded fixed top-5 right-5 z-50';
        warningDiv.role = 'alert';
        warningDiv.innerHTML = message;
        document.body.appendChild(warningDiv);
        
        this.updateDisplay();
        
        setTimeout(() => {
            warningDiv.remove();
        }, 3000);
    }
}

// Initialize monitoring
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM Content Loaded'); // Debug log
    window.testMonitoring = new TestMonitoring();
});