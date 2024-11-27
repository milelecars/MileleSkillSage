<x-app-layout>
    <div class="py-12 text-theme">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="p-6">
                    <h1 class="text-3xl font-bold text-gray-900 mb-6">Manage Candidates</h1>

                    @if(session('success'))
                        <div class="bg-amber-50 border-l-4 border-amber-200 text-amber-800 p-4 mb-4 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-blue-700">Total Candidates</h3>
                            <p class="text-2xl font-bold text-blue-900">{{ $totalCandidates }}</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-green-700">Completed Tests</h3>
                            <p class="text-2xl font-bold text-green-900">{{ $completedTests }}</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-700">Active Tests</h3>
                            <p class="text-2xl font-bold text-purple-900">{{ $activeTests }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-700">Total Reports</h3>
                            <p class="text-2xl font-bold text-gray-900">temp</p>
                        </div>
                    </div>

                    <!-- Candidates Table -->
                    <div class="overflow-x-auto rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 border border-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Candidate</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Test</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Started At</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Completed At</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Score</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Report</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 text-center">
                                @forelse($candidates as $candidate)
                                    <tr>
                                        <td class="px-2 py-4">
                                            <div class="text">{{ $candidate->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $candidate->email }}</div>
                                        </td>
                                        <td class="px-2 py-4 text-sm">{{ $candidate->tests->first()?->title ?? 'No Test Assigned' }}</td>
                                        <td class="px-2 py-4 text-sm">
                                            @if($candidate->test_completed_at)
                                                <span class="text-green-800 bg-green-100 px-2 py-1 rounded-full">Completed</span>
                                            @elseif($candidate->test_started_at)
                                                <span class="text-yellow-800 bg-yellow-100 px-2 py-1 rounded-full">In Progress</span>
                                            @else
                                                <span class="text-gray-800 bg-gray-100 px-2 py-1 rounded-full">Not Started</span>
                                            @endif
                                        </td>
                                        <td class="py-4 text-xs">
                                            {{ \Carbon\Carbon::parse($candidate->test_started_at)->format('M d, Y H:i') ?? '-'  }}
                                        </td>
                                        <td class="py-4 text-xs">
                                            {{ \Carbon\Carbon::parse($candidate->test_completed_at)->format('M d, Y H:i') ?? '-'  }}
                                        </td>

                                        <td class="px-2 py-4 text-sm">
                                            @if($candidate->test_completed_at)
                                                <div>{{ $candidate->test_score }} / {{ $candidate->total_questions }}</div>
                                            @else
                                                <span>-</span>
                                            @endif
                                        </td>
                                        <td class="py-4">
                                            <div class="flex space-x-4 justify-center text-sm">
                                                <!-- @if($candidate->test_completed_at)
                                                    <a href="{{ route('admin.candidate-result', $candidate->id) }}" class="text-blue-600">View Results</a>
                                                @endif -->
                                                <form action="{{ route('candidate.approve', $candidate->id) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <button type="submit" class="text-green-600">Approve</button>
                                                </form>
                                                <form action="{{ route('candidate.reject', $candidate->id) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <button type="submit" class="text-red-600">Reject</button>
                                                </form>
                                            </div>
                                        </td>
                                        <td class="flex py-6 items-center justify-center">
                                            <a href="{{ route('reports.candidate-report', ['candidateId' => $candidate->id, 'testId' => $candidate->test_id]) }}">
                                                <svg fill="#102141" width="25px" height="25px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="#102141" stroke-width="0.00024000000000000003"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="m20 8-6-6H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM9 19H7v-9h2v9zm4 0h-2v-6h2v6zm4 0h-2v-3h2v3zM14 9h-1V4l5 5h-4z"></path></g></svg>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No candidates found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $candidates->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
