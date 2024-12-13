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
            
            console.log('TestMonitoring initialized with:', {
                testId: this.testId,
                candidateId: this.candidateId,
                csrfToken: !!this.csrfToken
            });

            this.setupEventListeners();
            this.preventUserSelection();
        } catch (error) {
            console.error('Error initializing TestMonitoring:', error);
        }
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

    handleViolation(event, metricName, message) {
        if (event) {
            event.preventDefault();
        }
        console.log(`⚠️ ${message}`);
        
        // Convert metric name to flag type
        const flagType = this.getFlagTypeFromMetric(metricName);
        this.logSuspiciousBehavior(flagType);
    }

    getFlagTypeFromMetric(metricName) {
        // First add spaces before capital letters
        const withSpaces = metricName.replace(/([A-Z])/g, ' $1');
        // Capitalize the first letter, make rest lowercase, and trim spaces
        return withSpaces.charAt(0).toUpperCase() + 
               withSpaces.slice(1).toLowerCase().trim();
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
    }

    async logSuspiciousBehavior(flagType) {
        try {
            const livewireEl = document.querySelector('[wire\\:id]');
            if (!livewireEl || !window.Livewire) {
                console.warn('Livewire not initialized');
                return;
            }

            const component = window.Livewire.find(livewireEl.getAttribute('wire:id'));
            if (component) {
                await component.dispatch('logSuspiciousBehavior', [flagType]);
                console.log('Suspicious behavior logged:', flagType);
            }
        } catch (error) {
            console.warn('Error logging suspicious behavior:', error);
            console.error(error);
        }
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