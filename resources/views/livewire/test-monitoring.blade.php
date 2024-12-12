<div 
    x-data="{ 
        initMonitoring() {
            if (!window.testMonitoringInitialized) {
                window.testMonitoringInitialized = true;
                
                // Webcam violations
                document.addEventListener('webcamViolation', (event) => {
                    @this.logSuspiciousBehavior(event.detail.violation);
                    console.log('⚠️ Webcam violation detected:', event.detail.violation);
                });

                // Tab visibility
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) {
                        @this.logSuspiciousBehavior('Tab Switches');
                        console.log('⚠️ Tab Switching Detected!');
                    }
                });

                // Window blur
                window.addEventListener('blur', () => {
                    @this.logSuspiciousBehavior('Window Blurs');
                    console.log('⚠️ Window focus lost!');
                });

                // Mouse exit
                document.addEventListener('mouseleave', () => {
                    @this.logSuspiciousBehavior('Mouse Exits');
                    console.log('⚠️ Mouse exit detected!');
                });

                // Prevent copy/cut/paste
                ['copy', 'cut', 'paste'].forEach(event => {
                    document.addEventListener(event, (e) => {
                        e.preventDefault();
                        @this.logSuspiciousBehavior('Copy/Cut Attempts');
                        console.log(`⚠️ ${event} is not allowed!`);
                    });
                });

                // Prevent right click
                document.addEventListener('contextmenu', (e) => {
                    e.preventDefault();
                    @this.logSuspiciousBehavior('Right Clicks');
                    console.log('⚠️ Right clicking is not allowed!');
                });

                // Prevent keyboard shortcuts
                document.addEventListener('keydown', (e) => {
                    if ((e.ctrlKey || e.metaKey) && 
                        ['c', 'v', 'x', 'a', 'p', 'f12'].includes(e.key.toLowerCase())) {
                        e.preventDefault();
                        @this.logSuspiciousBehavior('Keyboard Shortcuts');
                        console.log('⚠️ Keyboard shortcut detected!', e.key);
                    }
                    
                    // Prevent F12 and other dev tools shortcuts
                    if (e.key === 'F12' || 
                        ((e.ctrlKey || e.metaKey) && e.shiftKey && (e.key === 'I' || e.key === 'C'))) {
                        e.preventDefault();
                        @this.logSuspiciousBehavior('Developer Tools');
                    }
                });
            }
        }
    }"
    x-init="initMonitoring()"
    class="mt-4 p-4 bg-red-200 rounded-lg shadow monitoring-summary"
>
    <h3 class="text-lg font-semibold">Test Monitoring Summary</h3>
    <div class="mt-2 grid grid-cols-2 gap-4">
        @foreach($flags->chunk(ceil($flags->count() / 2)) as $chunk)
            <div>
                @foreach($chunk as $flagType)
                    @php
                        $metricKey = lcfirst(str_replace(' ', '', $flagType->name));
                    @endphp
                    <p class="font-medium">
                        {{ $flagType->name }}: 
                        <span class="{{ $metrics[$metricKey] > $flagType->threshold ? 'text-red-600' : 'text-gray-600' }}">
                            {{ $metrics[$metricKey] }}
                        </span>
                        <br/>
                        <small>
                            Flagged: 
                            <span class="{{ $metrics[$metricKey] > $flagType->threshold ? 'text-red-600' : 'text-green-600' }}">
                                {{ $metrics[$metricKey] > $flagType->threshold ? 'Yes' : 'No' }}
                            </span>
                            <span class="text-xs text-gray-500">(Threshold: {{ $flagType->threshold }})</span>
                        </small>
                    </p>
                @endforeach
            </div>
        @endforeach
    </div>
</div>