<div class="mt-4 p-4 bg-red-200 rounded-lg shadow monitoring-summary">
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
                        <span data-metric="{{ $metricKey }}" class="text-gray-600">0</span>
                        <br/>
                        <small>
                            Flagged: 
                            <span data-metric-flag="{{ $metricKey }}" class="text-green-600">No</span>
                            <span class="text-xs text-gray-500">(Threshold: {{ $flagType->threshold }})</span>
                        </small>
                    </p>
                @endforeach
            </div>
        @endforeach
    </div>
</div>