<x-app-layout>
    <div class="py-12 text-theme">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="p-6">
                    
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-3xl font-bold text-gray-900">Manage Candidates</h1>
                        
                        <!-- search functionality  -->
                        <form method="GET" action="{{ route('admin.manage-candidates') }}" class="flex gap-2">
                            <div class="relative">
                                <input
                                    type="text"
                                    name="search"
                                    value="{{ $search ?? '' }}"
                                    placeholder="Search by name or email..."
                                    class="w-64 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                >
                                @if($search)
                                    <a href="{{ route('admin.manage-candidates') }}" 
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                                    title="Clear search">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                            <button type="submit" class="px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Search
                            </button>
                        </form>
                    </div>

                    @if(session('success'))
                        <div class="bg-amber-50 border-l-4 border-amber-200 text-amber-800 p-4 mb-4 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-blue-700">Total Invited</h3>
                            <p class="text-2xl font-bold text-blue-900">{{ $totalInvited }}</p>
                            <p class="text-sm text-blue-600 mt-1">Total invitations sent</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-green-700">Completed Tests</h3>
                            <p class="text-2xl font-bold text-green-900">{{ $completedTestsCount }}</p>
                            <p class="text-sm text-green-600 mt-1">Tests finished by candidates</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-700">Active Tests</h3>
                            <p class="text-2xl font-bold text-purple-900">{{ $activeTests }}</p>
                            <p class="text-sm text-purple-600 mt-1">Currently active tests</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-700">Total Reports</h3>
                            <p class="text-2xl font-bold text-gray-900">{{ $totalReports }}</p>
                            <p class="text-sm text-gray-600 mt-1">Generated test reports</p>
                        </div>
                    </div>

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
                                            @if($candidate['has_started'])
                                            <a href="{{ route('admin.candidate-result', ['test' => $candidate['test_id'], 'candidate' => $candidate['id']]) }}" class="hover:text-blue-600">
                                                <div class="text">{{ $candidate['name'] }}</div>
                                                <div class="text-xs text-gray-500">{{ $candidate['email'] }}</div>
                                            </a>
                                            @else
                                                <div class="text-xs text-gray-500">{{ $candidate['email'] }}</div>
                                            @endif
                                        </td>
                                        <td class="px-2 py-4 text-sm">{{ $candidate['test_title'] }}</td>
                                        @if(!$candidate['has_started'])
                                            <td class="px-2 py-4 text-sm" colspan="6">
                                                <span class="text-gray-800 bg-gray-100 px-2 py-1 rounded-full">Not Started</span>
                                            </td>
                                        @else
                                            <td class="px-2 py-4 text-sm">
                                                @if($candidate['status'] === 'accepted')
                                                    <span class="text-green-800 bg-green-100 px-2 py-1 rounded-full">Accepted</span>
                                                @elseif($candidate['status'] === 'rejected')
                                                    <span class="text-red-800 bg-red-100 px-2 py-1 rounded-full">Rejected</span>
                                                @elseif($candidate['status'] === 'completed') 
                                                    <span class="text-blue-800 bg-blue-100 px-2 py-1 rounded-full">Completed</span>
                                                @elseif($candidate['status'] === 'in_progress')
                                                    <span class="text-yellow-800 bg-yellow-100 px-2 py-1 rounded-full">In Progress</span>
                                                @endif
                                            </td>
                                            <td class="py-4 text-xs">
                                                {{ isset($candidate['started_at']) ? \Carbon\Carbon::parse($candidate['started_at'])->format('M d, Y H:i') : '-' }}
                                            </td>
                                            <td class="py-4 text-xs">
                                                {{ isset($candidate['completed_at']) ? \Carbon\Carbon::parse($candidate['completed_at'])->format('M d, Y H:i') : '-' }}
                                            </td>
                                            <td class="px-2 py-4 text-sm">
                                                @if(isset($candidate['completed_at']))
                                                    <div>{{ $candidate['score'] }} / {{ $candidate['total_questions'] }}</div>
                                                @else
                                                    <span>-</span>
                                                @endif
                                            </td>
                                            <td class="py-4">
                                                <div class="flex space-x-4 justify-center text-sm">
                                                    @if(!in_array($candidate['status'], ['accepted', 'rejected']))
                                                        <form action="{{ route('candidate.accept', $candidate['id']) }}" method="POST">
                                                            @csrf @method('PUT')
                                                            <input type="hidden" name="test_id" value="{{ $candidate['test_id'] }}">
                                                            <button type="submit" class="text-green-600">Accept</button>
                                                        </form>
                                                        <form action="{{ route('candidate.reject', $candidate['id']) }}" method="POST">
                                                            @csrf @method('PUT')
                                                            <input type="hidden" name="test_id" value="{{ $candidate['test_id'] }}">
                                                            <button type="submit" class="text-red-600">Reject</button>
                                                        </form>
                                                    @elseif($candidate['status'] === 'accepted')
                                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    @elseif($candidate['status'] === 'rejected')
                                                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="flex py-6 items-center justify-center">
                                                @if(isset($candidate['completed_at']))
                                                    <a href="{{ route('reports.candidate-report', ['candidateId' => $candidate['id'], 'testId' => $candidate['test_id']]) }}">
                                                        <svg fill="#102141" width="25px" height="25px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="#102141" stroke-width="0.00024000000000000003"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="m20 8-6-6H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM9 19H7v-9h2v9zm4 0h-2v-6h2v6zm4 0h-2v-3h2v3zM14 9h-1V4l5 5h-4z"></path></g></svg>
                                                    </a>
                                                @else
                                                    <span>-</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">No candidates found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $candidates->appends(['search' => $search])->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>