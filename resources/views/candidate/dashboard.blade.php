<x-app-layout>
    <div class="text-theme" id="dashboard-container">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- header --}}
            <div class="p-6 pb-4 mt-5 mb-10 border-b-2 border-gray-800">
                <h1 class="text-3xl font-bold text-gray-900">
                    Welcome, {{ Auth::guard('candidate')->user()->name}} 👋
                </h1>
                <div class="text-sm text-gray-600 mt-2 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                    </svg>
                    {{ Auth::guard('candidate')->user()->email }}
                </div>
            </div>

            {{-- content --}}
            <div class="bg-white shadow rounded-lg">
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-4">Your Tests</h2>
                    
                    @if($candidateTests->isEmpty())
                        <div class="text-gray-500 text-center py-4">
                            No tests available yet.
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 border border-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-base font-semibold text-gray-500 uppercase">Test Title</th>
                                        <th class="px-4 py-3 text-base font-semibold text-gray-500 uppercase">Status</th>
                                        <th class="px-4 py-3 text-base font-semibold text-gray-500 uppercase">Started At</th>
                                        <th class="px-4 py-3 text-base font-semibold text-gray-500 uppercase">Completed At</th>
                                        <th class="px-4 py-3 text-base font-semibold text-gray-500 uppercase">Score</th>
                                        <th class="px-4 py-3 text-base font-semibold text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($candidateTests as $test)
                                        <tr class="text-center">
                                            <td class="px-2 py-4 text-base">
                                                <div class="font-medium text-gray-900">{{ $test['title'] }}</div>
                                                <div class="text-base text-gray-500">{{ $test['questions_count'] }} questions</div>
                                            </td>
                                            <td class="px-2 py-4 text-base">
                                                <span class="px-2 inline-flex text-base leading-5 font-semibold rounded-full 
                                                    @if($test['status'] === 'completed') bg-green-100 text-green-800
                                                    @elseif($test['status'] === 'in_progress') bg-yellow-100 text-yellow-800
                                                    @elseif($test['status'] === 'not_started') bg-gray-100 text-gray-800
                                                    @elseif($test['status'] === 'expired') bg-red-100 text-red-800
                                                    @endif">
                                                    {{ ucfirst(str_replace('_', ' ', $test['status'])) }}
                                                </span>
                                            </td>
                                            <td class="px-2 py-4 text-base">
                                                {{ $test['started_at'] ? \Carbon\Carbon::parse($test['started_at'])->format('M d, Y H:i') : '-' }}
                                            </td>
                                            <td class="px-2 py-4 text-base">
                                                {{ $test['completed_at'] ? \Carbon\Carbon::parse($test['completed_at'])->format('M d, Y H:i') : '-' }}
                                            </td>
                                            <td class="px-2 py-4 text-base">
                                                @if($test['score'] !== null)
                                                    <span class="font-medium">
                                                        {{ $test['score'] }}{{ $test['hasMCQ'] ? '%' : '' }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-2 py-4">
                                                <div class="flex justify-center gap-2">
                                                    @if($test['status'] === 'expired')
                                                        <span>-</span>
                                                    @elseif(!in_array($test['status'], ['completed', 'accepted', 'rejected']))
                                                        <a href="{{ route('tests.setup', $test['test_id']) }}" class="text-blue-600 hover:text-blue-800"> 
                                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                                            </svg>
                                                        </a>
                                                    @else
                                                        <a href="{{ route('tests.result', $test['test_id']) }}" class="text-blue-600 hover:text-blue-800">
                                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                            </svg>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
           
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const viewGuidelinesBtn = document.getElementById('view-guidelines-btn');
            const continueTestBtn = document.getElementById('continue-test-btn');
            const cameraWarning = document.getElementById('camera-warning');

            function updateButtonVisibility(personCount, hasBook, hasCellPhone) {
                if (viewGuidelinesBtn) {
                    viewGuidelinesBtn.style.display = (personCount === 1 && !hasBook && !hasCellPhone) ? 'inline-flex' : 'none';
                }

                if (continueTestBtn) {
                    continueTestBtn.style.display = (personCount === 1 && !hasBook && !hasCellPhone) ? 'inline-flex' : 'none';
                }

                cameraWarning.style.display = (personCount === 0) ? 'flex' : 'none';
            }

            document.addEventListener('webcamStatusUpdate', function(e) {
                updateButtonVisibility(e.detail.personCount, e.detail.hasBook, e.detail.hasCellPhone);
            });
        });

    </script>
</x-app-layout>