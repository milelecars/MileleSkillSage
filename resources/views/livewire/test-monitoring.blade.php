<div 
    x-data="{ 
        initMonitoring() {
            document.addEventListener('webcamViolation', (event) => {
                @this.logSuspiciousBehavior(event.detail.violation);
                console.log('⚠️ Webcam violation detected:', event.detail.violation);
            });

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    @this.logSuspiciousBehavior('Tab Switches');
                    console.log('⚠️ Tab Switching Detected!');
                }
            });

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    @this.logSuspiciousBehavior('Tab Switches');
                    console.log('⚠️ Tab Switching Detected!');
                }
            });

            window.addEventListener('blur', () => {
                @this.logSuspiciousBehavior('Window Blurs');
                console.log('⚠️ Window focus lost!');
            });

            document.addEventListener('mouseleave', () => {
                @this.logSuspiciousBehavior('Mouse Exits');
                console.log('⚠️ Mouse exit detected!');
            });

            document.addEventListener('copy', (e) => {
                e.preventDefault();
                @this.logSuspiciousBehavior('Copy/Cut Attempts');
                console.log('⚠️ Copying is not allowed!');
            });

            document.addEventListener('cut', (e) => {
                e.preventDefault();
                @this.logSuspiciousBehavior('Copy/Cut Attempts');
                console.log('⚠️ Cutting is not allowed!');
            });

            document.addEventListener('contextmenu', (e) => {
                e.preventDefault();
                @this.logSuspiciousBehavior('Right Clicks');
                console.log('⚠️ Right clicking is not allowed!');
            });

            document.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && 
                    ['c', 'v', 'x', 'a', 'p', 'p'].includes(e.key.toLowerCase())) {
                    e.preventDefault();
                    @this.logSuspiciousBehavior('Keyboard Shortcuts');
                    console.log('⚠️ Keyboard shortcut detected!', e.key);
                }
            });
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