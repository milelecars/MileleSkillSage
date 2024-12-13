// Initialize global monitoring state immediately
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
        if (window.testMonitoring) {
            console.log('TestMonitoring already initialized, returning existing instance');
            return window.testMonitoring;
        }

        window.testMonitoring = this;
        
        try {
            this.testId = testId;
            this.candidateId = candidateId;
            this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            // Initialize metrics from the Livewire component's state
            this.metrics = { ...window.monitoringData.metrics };
            
            // Load initial metrics from DOM
            this.loadMetricsFromDOM();
            
            // Sync with global state
            window.monitoringData.metrics = { ...this.metrics };
            
            this.flags = {};
            
            console.log('TestMonitoring initialized with:', {
                testId: this.testId,
                candidateId: this.candidateId,
                csrfToken: !!this.csrfToken,
                metrics: this.metrics
            });

            this.setupEventListeners();
            this.startPeriodicSync();
            this.preventUserSelection();
        } catch (error) {
            console.error('Error initializing TestMonitoring:', error);
        }
    }

    loadMetricsFromDOM() {
        // Load metrics from the Livewire-rendered DOM elements
        const metricElements = document.querySelectorAll('[data-metric]');
        metricElements.forEach(element => {
            const metricName = element.dataset.metric;
            const value = parseInt(element.textContent) || 0;
            this.metrics[metricName] = value;
            window.monitoringData.metrics[metricName] = value;
        });
    }

    preventUserSelection() {
        document.body.style.userSelect = 'none';
        document.body.style.webkitUserSelect = 'none';
        document.body.style.msUserSelect = 'none';
        document.body.style.mozUserSelect = 'none';

        document.querySelectorAll('input, textarea').forEach(element => {
            element.style.userSelect = 'text';
            element.style.webkitUserSelect = 'text';
            element.style.msUserSelect = 'text';
            element.style.mozUserSelect = 'text';
        });
    }

    updateMetric(metricName, value) {
        this.metrics[metricName] = value;
        window.monitoringData.metrics[metricName] = value;
        
        // Update Livewire component state through Alpine.js
        const livewireComponent = document.querySelector('[wire\\:id]')?.__livewire;
        if (livewireComponent) {
            livewireComponent.set('metrics.' + metricName, value);
        }
        
        this.checkIfFlagged();
        this.updateDisplay();
    }

    handleViolation(event, metricName, message) {
        if (event) {
            event.preventDefault();
        }
        const newValue = this.metrics[metricName] + 1;
        this.updateMetric(metricName, newValue);
        console.log(`⚠️ ${message}`, newValue);
        this.logSuspiciousBehavior(metricName);
    }

    setupEventListeners() {
        document.addEventListener('visibilitychange', (e) => {
            if (document.hidden) {
                this.handleViolation(e, 'tabSwitches', 'Tab Switching Detected!');
            }
        });
    
        window.addEventListener('blur', (e) => {
            this.handleViolation(e, 'windowBlurs', 'Window focus lost!');
        });
    
        document.addEventListener('mouseleave', (e) => {
            this.handleViolation(e, 'mouseExits', 'Mouse exit detected!');
        });
    
        ['copy', 'cut'].forEach(eventType => {
            document.addEventListener(eventType, (e) => {
                this.handleViolation(e, 'copyCutAttempts', `${eventType} is not allowed!`);
            });
        });
    
        document.addEventListener('contextmenu', (e) => {
            this.handleViolation(e, 'rightClicks', 'Right clicking is not allowed!');
        });
    
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && 
                ['c', 'v', 'x', 'a', 'p', 'f12'].includes(e.key.toLowerCase())) {
                this.handleViolation(e, 'keyboardShortcuts', `Keyboard shortcut detected: ${e.key}`);
            }
            
            if (e.key === 'F12' || 
                ((e.ctrlKey || e.metaKey) && e.shiftKey && (e.key === 'I' || e.key === 'C'))) {
                this.handleViolation(e, 'keyboardShortcuts', 'Developer Tools shortcut detected!');
            }
        });

        // Listen for Livewire updates
        document.addEventListener('livewire:initialized', () => {
            this.loadMetricsFromDOM();
        });
    }

    async logSuspiciousBehavior(flagType) {
        try {
            if (!this.csrfToken || !this.testId) {
                console.warn('Missing required data for logging:', {
                    csrfToken: !!this.csrfToken,
                    testId: this.testId
                });
                return;
            }

            // Call Livewire component's method directly
            const livewireComponent = document.querySelector('[wire\\:id]')?.__livewire;
            if (livewireComponent) {
                await livewireComponent.call('logSuspiciousBehavior', flagType);
            }

        } catch (error) {
            console.warn('Error logging behavior:', error);
        }
    }

    updateDisplay() {
        const summaryDiv = document.querySelector('.monitoring-summary');
        if (summaryDiv) {
            Object.entries(this.metrics).forEach(([key, value]) => {
                const element = document.querySelector(`[data-metric="${key}"]`);
                if (element) {
                    element.textContent = value;
                    const threshold = this.getThreshold(key);
                    element.className = value > threshold ? 'text-red-600' : 'text-gray-600';
                }

                const flagElement = document.querySelector(`[data-metric-flag="${key}"]`);
                if (flagElement) {
                    const isThisMetricFlagged = value > this.getThreshold(key);
                    this.flags[key] = isThisMetricFlagged;
                    flagElement.textContent = isThisMetricFlagged ? 'Yes' : 'No';
                    flagElement.className = isThisMetricFlagged ? 'text-red-600' : 'text-green-600';
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
        this.updateMetric('warningCount', this.metrics.warningCount + 1);
        
        const warningDiv = document.createElement('div');
        warningDiv.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded fixed top-5 right-5 z-50';
        warningDiv.role = 'alert';
        warningDiv.innerHTML = message;
        document.body.appendChild(warningDiv);
        
        setTimeout(() => {
            warningDiv.remove();
        }, 3000);
    }
}

// Initialize monitoring when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    try {
        const testIdElement = document.getElementById('test-id');
        const candidateIdElement = document.getElementById('candidate-id');
        
        if (testIdElement && candidateIdElement) {
            new TestMonitoring(
                testIdElement.value,
                candidateIdElement.value
            );
        } else {
            console.warn('Test monitoring not initialized - required elements not found', {
                testIdFound: !!testIdElement,
                candidateIdFound: !!candidateIdElement
            });
        }
    } catch (error) {
        console.error('Error during TestMonitoring initialization:', error);
    }
});

window.TestMonitoring = TestMonitoring;