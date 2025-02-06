<x-app-layout>
    <div class="py-12 text-theme">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="p-6">
                    
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Manage Candidates</h1>
                        
                        <!-- search functionality  -->
                        <form method="GET" action="{{ route('admin.manage-candidates') }}" class="flex gap-2">
                            <select name="test_filter" class="w-48 h-9 border text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Tests</option>
                                @foreach($availableTests as $test)
                                    <option value="{{ $test->id }}" {{ $testFilter == $test->id ? 'selected' : '' }}>
                                        {{ $test->title }}
                                    </option>
                                @endforeach
                            </select>

                            <div class="relative">
                                <input
                                    type="text"
                                    name="search"
                                    value="{{ $search ?? '' }}"
                                    placeholder="Search by name or email..."
                                    class="w-64 h-9 border text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
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

                            <button type="submit" class="px-3 h-9 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700">
                                Search
                            </button>

                            @if($search || $testFilter)
                                <a href="{{ route('admin.manage-candidates') }}" 
                                class="px-3 h-9 border border-gray-300 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 flex items-center">
                                    Clear Filters
                                </a>
                            @endif
                        </form>
                    </div>

                    <!-- @if(session('success'))
                        <div class="bg-amber-50 border-l-4 border-amber-200 text-amber-800 p-4 mb-4 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif -->

                    <!-- Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-blue-700">Total Invited</h3>
                            <p class="text-2xl font-bold text-blue-900">{{ $totalInvited }}</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-green-700">Completed Tests</h3>
                            <p class="text-2xl font-bold text-green-900">{{ $completedTestsCount }}</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-700">Active Tests</h3>
                            <p class="text-2xl font-bold text-purple-900">{{ $activeTests }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-700">Total Reports</h3>
                            <p class="text-2xl font-bold text-gray-900">{{ $totalReports }}</p>
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
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Report</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
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
                                        
                                        <td class="px-2 py-4 text-sm">
                                            @if($candidate['status'] === 'accepted')
                                                <span class="text-green-800 bg-green-100 px-2 py-1 rounded-full">Accepted</span>
                                            @elseif($candidate['status'] === 'rejected')
                                                <span class="text-red-800 bg-red-100 px-2 py-1 rounded-full">Rejected</span>
                                            @elseif($candidate['status'] === 'completed')
                                                <span class="text-blue-800 bg-blue-100 px-2 py-1 rounded-full">Completed</span>
                                            @elseif($candidate['status'] === 'in_progress')
                                                <span class="text-yellow-800 bg-yellow-100 px-2 py-1 rounded-full">In Progress</span>
                                            @elseif($candidate['status'] === 'expired')
                                                <span class="text-red-800 bg-red-100 px-2 py-1 rounded-full">Expired</span>
                                            @else
                                                <span class="text-gray-800 bg-gray-100 px-2 py-1 rounded-full">Not Started</span>
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
                                                <div>{{ $candidate['correct_answers'] }} / {{ $candidate['total_questions'] }}</div>
                                            @else
                                                <span>-</span>
                                            @endif
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

                                        <td class="py-4">
                                            <div class="relative" x-data="{ open: false, showDeadlineModal: false }">
                                                @if($candidate['status'] === 'completed')
                                                    <button @click="open = !open" class="text-gray-600 hover:text-gray-800">
                                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                                        </svg>
                                                    </button>
                                                    
                                                    <div x-show="open" 
                                                        @click.away="open = false" 
                                                        class="absolute right-0 mt-2 w-36 bg-white rounded-md shadow-lg py-1 z-50">
                                                        
                                                        <form action="{{ route('candidate.accept', $candidate['id']) }}" method="POST" class="block"
                                                            onsubmit="return confirm('Are you sure you want to accept this candidate?');">
                                                            @csrf @method('PUT')
                                                            <input type="hidden" name="test_id" value="{{ $candidate['test_id'] }}">
                                                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-green-700 hover:bg-gray-100">
                                                                Accept
                                                            </button>
                                                        </form>

                                                        <form action="{{ route('candidate.reject', $candidate['id']) }}" method="POST" class="block"
                                                            onsubmit="return confirm('Are you sure you want to reject this candidate?');">
                                                            @csrf @method('PUT')
                                                            <input type="hidden" name="test_id" value="{{ $candidate['test_id'] }}">
                                                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-orange-600 hover:bg-gray-100">
                                                                Reject
                                                            </button>
                                                        </form>

                                                        <form action="{{ route('candidate.delete', [$candidate['id'], $candidate['test_id']]) }}" method="POST" class="block"
                                                            onsubmit="return confirm('Are you sure you want to delete this candidate?');">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                                                Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                @elseif($candidate['status'] === 'expired')
                                                    <button @click="showDeadlineModal = true" class="text-blue-600 hover:text-blue-800 text-sm">
                                                        Extend Deadline
                                                    </button>

                                                    <!-- Deadline Extension Modal -->
                                                    <div x-show="showDeadlineModal" 
                                                        class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center">
                                                        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                                                            <h3 class="text-lg font-semibold mb-6">Extend Deadline</h3>

                                                            <div class="flex gap-2 mb-4">
                                                                <span class="text-gray-600">Current Deadline:</span>
                                                                <span class="block font-medium">{{ \Carbon\Carbon::parse($candidate['expiration_date'])->format('M d, Y H:i') }}</span>
                                                            </div>
                                                            
                                                            <form action="{{ route('invitations.extend-deadline') }}" method="POST">
                                                                @csrf
                                                                <input type="hidden" name="test_id" value="{{ $candidate['test_id'] }}">
                                                                <input type="hidden" name="email" value="{{ $candidate['email'] }}">
                                                                
                                                                <div class="flex gap-2 items-center mb-4">
                                                                    <label class="text-gray-600">New Deadline:</label>
                                                                    <input type="datetime-local" 
                                                                        name="new_deadline" 
                                                                        class=" border border-gray-300 rounded-md p-2"
                                                                        min="{{ now()->format('Y-m-d\TH:i') }}"
                                                                        required>
                                                                </div>
                                                                
                                                                <div class="flex justify-end space-x-3">
                                                                    <button type="button" 
                                                                            @click="showDeadlineModal = false"
                                                                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                                                                        Cancel
                                                                    </button>
                                                                    <button type="submit"
                                                                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                                                                        Update Deadline
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </div>
                                        </td>
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