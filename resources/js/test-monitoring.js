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
            this.metrics = {};
            this.violationThreshold = 3; 
            this.violationCounts = {
                windowBlurs: 0,
                tabSwitches: 0,
                windowMinimizations: 0
            };
            
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
        
        this.metrics[metricName] = (this.metrics[metricName] || 0) + 1;
        const currentCount = this.metrics[metricName];
        this.violationCounts[metricName] = (this.violationCounts[metricName] || 0) + 1;
        
        console.log(`⚠️ ${message} (Count: ${currentCount})`);
        this.updateViolationLog(`${message} (${currentCount})`);
        
        if (this.violationCounts[metricName] >= this.violationThreshold) {
            this.suspendTest(metricName);
        }

        const flagType = this.getFlagTypeFromMetric(metricName);
        this.logSuspiciousBehavior(flagType, currentCount);
    }

    // Suspension
    async suspendTest(violationType) {
        console.log(`Test suspended due to excessive ${violationType}`);
    
        try { 
            await this.logSuspension(violationType);
            
            const response = await fetch('/get-unsuspend-count', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: JSON.stringify({
                    testId: this.testId,
                    candidateId: this.candidateId,
                }),
            });
    
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
    
            const data = await response.json();
    
            if (data.unsuspend_count == 1) {
                window.location.href = '/candidate/dashboard';
                return;
            }else{
                window.location.href = `/tests/${this.testId}/suspended?reason=${violationType}`;
            }
    
           
    
        } catch (error) {
            console.error('Error during suspension:', error);
        }
    }
    
    // async calculateRemainingTime() {
    //     try {
    //         // Fetch the started_at time from the candidate_test table
    //         const response = await fetch('/get-test-start-time', {
    //             method: 'POST',
    //             headers: {
    //                 'Content-Type': 'application/json',
    //                 'X-CSRF-TOKEN': this.csrfToken,
    //             },
    //             body: JSON.stringify({
    //                 testId: this.testId,
    //                 candidateId: this.candidateId,
    //             }),
    //         });
    
    //         if (!response.ok) {
    //             throw new Error(`HTTP error! status: ${response.status}`);
    //         }
    
    //         const data = await response.json();
    //         const startedAt = new Date(data.started_at); // Parse the start time
    //         const testDuration = parseInt(sessionStorage.getItem('test_duration'), 10); // Total test duration in seconds
    
    //         // Check if the values are valid
    //         if (isNaN(startedAt.getTime())) {
    //             console.error('Invalid started_at time:', data.started_at);
    //             return 0; // Return 0 or handle the error appropriately
    //         }
    
    //         if (isNaN(testDuration)) {
    //             console.error('Invalid test_duration:', testDuration);
    //             return 0; // Return 0 or handle the error appropriately
    //         }
    
    //         // Get the current time
    //         const now = new Date();
    
    //         // Calculate the elapsed time since the test started (in seconds)
    //         const elapsedTime = Math.floor((now - startedAt) / 1000);
    
    //         // Calculate the remaining time by deducting the elapsed time from the total test duration
    //         const remainingTimeInSeconds = Math.max(0, testDuration - elapsedTime);
    
    //         // Convert remaining time from seconds to minutes (rounded up)
    //         const remainingTimeInMinutes = Math.ceil(remainingTimeInSeconds / 60);
    
    //         console.log('Remaining time in minutes:', remainingTimeInMinutes);
    
    //         return remainingTimeInMinutes; // Return the remaining time in minutes
    //     } catch (error) {
    //         console.error('Error calculating remaining time:', error);
    //         return 0; // Return 0 or handle the error appropriately
    //     }
    // }
    
    async logSuspension(violationType) {
        try {
            console.log('Sending suspension data to backend:', {
                testId: this.testId,
                candidateId: this.candidateId,
                violationType: violationType,
            });
    
            const response = await fetch('/log-suspension', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: JSON.stringify({
                    testId: this.testId,
                    candidateId: this.candidateId,
                    violationType: violationType,
                }),
            });
    
            if (!response.success) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            // if (response.success) {
            //     window.location.href = `/tests/${this.testId}/suspended?reason=${violationType}`;
            //     console.log('Suspension logged successfully');

            // }
        } catch (error) {
            console.error('Error logging suspension:', error);
        }
    }

    updateViolationLog(message) {
        const logDiv = document.getElementById('violation-log');
        if (logDiv) {
            logDiv.textContent = message;
        }
    }

    getFlagTypeFromMetric(metricName) {
        const withSpaces = metricName.replace(/([A-Z])/g, ' $1');
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

    async logSuspiciousBehavior(flagType, occurrences) {
        try {
            const response = await fetch('/flag', { 
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    candidateId: this.candidateId,
                    testId: this.testId,
                    flagType: flagType,
                    occurrences: occurrences,
                    isFlagged: true
                })
            });
    
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
    
            const data = await response.json();
            console.log('Violation logged to backend:', data);
        } catch (error) {
            console.error('Error logging violation:', error);
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