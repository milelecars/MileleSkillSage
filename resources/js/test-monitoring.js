class TestMonitoring {
    constructor(testId, candidateId) {
        this.testId = testId;
        this.candidateId = candidateId;
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        console.log('TestMonitoring initialized');
        this.metrics = {};
        this.flags = {};
    
        // Dynamically create metrics and flags based on flag types
        const flagTypeNames = [
            'Tab Switches', 
            'Window Blurs', 
            'Mouse Exits', 
            'Copy/Cut Attempts', 
            'Right Clicks', 
            'Keyboard Shortcuts',
            'More than One Person',
            'Book',
            'Cellphone'
        ];
    
        flagTypeNames.forEach(flagType => {
            const camelCaseName = flagType.replace(/\s+/g, '');
            this.metrics[camelCaseName] = 0;
            this.flags[camelCaseName] = false;
        });
    
        window.monitoringData = {
            metrics: this.metrics,
            flags: this.flags
        };
    
        this.setupEventListeners();
        this.startPeriodicSync();
    }


    async logSuspiciousBehavior(flagType) {
        if (!this.csrfToken || !window.testSessionId) {
            return;
        }

        try {
            await fetch('/flag', { 
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({
                    flag_type: flagType,
                    test_session_id: window.testSessionId 
                })
            });
        } catch (error) {
            console.warn('Error logging behavior:', error);
        }
    }

    setupEventListeners() {
        // Tab Switching
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.metrics.tabSwitches++;
                window.monitoringData.metrics.tabSwitches = this.metrics.tabSwitches;
                this.checkIfFlagged();
                console.log('⚠️ Tab Switching Detected!', this.metrics.tabSwitches);
                this.updateDisplay();
                this.logSuspiciousBehavior('Tab Switches');
            }
        });
    
        // Window Blur 
        window.addEventListener('blur', () => {
            this.metrics.windowBlurs++;
            window.monitoringData.metrics.windowBlurs = this.metrics.windowBlurs;
            this.checkIfFlagged();
            console.log('⚠️ Window focus lost!', this.metrics.windowBlurs);
            this.updateDisplay();
            this.logSuspiciousBehavior('Window Blurs');
        });
    
        // Mouse Leaving the window 
        document.addEventListener('mouseleave', () => {
            this.metrics.mouseExits++;
            window.monitoringData.metrics.mouseExits = this.metrics.mouseExits;
            this.checkIfFlagged();
            console.log('⚠️ Mouse exit detected!', this.metrics.mouseExits);
            this.updateDisplay();
            this.logSuspiciousBehavior('Mouse Exits');
        });
    
        // Detect copy/cut attempts
        document.addEventListener('copy', (e) => {
            e.preventDefault();
            this.metrics.copyCutAttempts++;
            window.monitoringData.metrics.copyCutAttempts = this.metrics.copyCutAttempts;
            this.checkIfFlagged();
            console.log('⚠️ Copying is not allowed!', this.metrics.copyCutAttempts);
            this.updateDisplay();
            this.logSuspiciousBehavior('Copy/Cut Attempts');
        });
    
        document.addEventListener('cut', (e) => {
            e.preventDefault();
            this.metrics.copyCutAttempts++;
            window.monitoringData.metrics.copyCutAttempts = this.metrics.copyCutAttempts;
            this.checkIfFlagged();
            console.log('⚠️ Cutting is not allowed!', this.metrics.copyCutAttempts);
            this.updateDisplay();
            this.logSuspiciousBehavior('Copy/Cut Attempts');
        });
    
        // Right Click Attempt 
        // document.addEventListener('contextmenu', (e) => {
        //     e.preventDefault();
        //     this.metrics.rightClicks++;
        //     console.log('⚠️ Right clicking is not allowed!', this.metrics.rightClicks);
        //     this.updateDisplay();
        //     this.logSuspiciousBehavior('Right Clicks');
        // });
    
        // Detect keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && 
                ['c', 'v', 'x', 'a', 'p', 'p'].includes(e.key.toLowerCase())) {
                e.preventDefault();
                this.metrics.keyboardShortcuts++;
                window.monitoringData.metrics.keyboardShortcuts = this.metrics.keyboardShortcuts;
                this.checkIfFlagged();
                console.log('⚠️ Keyboard shortcut detected!', e.key, this.metrics.keyboardShortcuts);
                this.updateDisplay();
                this.logSuspiciousBehavior('Keyboard Shortcuts');
            }
        });
    }

    updateDisplay() {
        // console.log('Updating display with metrics:', this.metrics);
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
                // Send flag data when a metric exceeds threshold
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
    console.log('DOM Content Loaded');
    const testId = document.getElementById('test-id').value;
    const candidateId = document.getElementById('candidate-id').value;
    window.testMonitoring = new TestMonitoring(testId, candidateId);
});