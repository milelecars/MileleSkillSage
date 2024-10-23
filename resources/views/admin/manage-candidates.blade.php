<x-app-layout>
    <div class="py-12 text-theme">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="p-6">
                    <h1 class="text-3xl font-bold text-gray-900 mb-6">Manage Candidates</h1>

                    @if(session('success'))
                        <div class="bg-amber-50 border-l-4 border-amber-200 text-amber-800 p-4 mb-4 rounded-lg">
                            {{ session('success') }}.
                        </div>
                    @endif

                    <!-- Basic Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-blue-700">Total Candidates</h3>
                            <p class="text-2xl font-bold text-blue-900">{{ $totalCandidates ?? 0 }}</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-green-700">Completed Tests</h3>
                            <p class="text-2xl font-bold text-green-900">{{ $completedTests ?? 0 }}</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-700">Active Tests</h3>
                            <p class="text-2xl font-bold text-purple-900">{{ $activeTests ?? 0 }}</p>
                        </div>
                    </div>

                    <!-- Candidates Table -->
                    <div class="overflow-x-auto rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap w-1/5 border-r border-gray-200">
                                        Candidate
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap w-1/7 border-r border-gray-200">
                                        Test Name
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap w-1/8 border-r border-gray-200">
                                        Status
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap w-1/7 border-r border-gray-200">
                                        Started At
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap w-1/7 border-r border-gray-200">
                                        Completed At
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap w-1/8 border-r border-gray-200">
                                        Score
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase whitespace-nowrap w-1/4">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($candidates as $candidate)
                                <tr>
                                    <td class="px-4 py-4 border-r border-gray-200">
                                        <div class="flex flex-col items-center">
                                            <div class="text-sm font-medium text-gray-900">{{ $candidate->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $candidate->email }}</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center border-r border-gray-200">
                                        <div class="text-sm text-gray-900">{{ $candidate->test_name ?? 'No test' }}</div>
                                    </td>
                                    <td class="px-4 py-4 border-r border-gray-200">
                                        <div class="flex flex-col items-center gap-1">
                                            @if($candidate->test_completed_at)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Completed
                                                </span>
                                                @if($candidate->tests->first()?->pivot?->is_expired)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Time Expired
                                                    </span>
                                                @endif
                                            @elseif($candidate->test_started_at)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    In Progress
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    Not Started
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-500 text-center border-r border-gray-200">
                                        {{ $candidate->test_started_at ? $candidate->test_started_at->format('M d, Y H:i') : '-' }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-500 text-center border-r border-gray-200">
                                        {{ $candidate->test_completed_at ? $candidate->test_completed_at->format('M d, Y H:i') : '-' }}
                                    </td><td class="px-5 py-4 text-center border-r border-gray-200">
                                        @if($candidate->test_completed_at)
                                            @if($test = $candidate->tests->first())
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $test->pivot->score ?? $candidate->test_score }}
                                                    <span class="text-gray-500">/ {{ $candidate->total_questions }}</span>
                                                </div>
                                                @if($candidate->total_questions > 0)
                                                    <div class="mt-1">
                                                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                            <div class="bg-blue-600 h-1.5 rounded-full" 
                                                                 style="width: {{ ($candidate->test_score / $candidate->total_questions * 100) }}%">
                                                            </div>
                                                        </div>
                                                        <div class="text-xs text-gray-500 mt-1">
                                                            {{ number_format(($candidate->test_score / $candidate->total_questions * 100), 1) }}%
                                                        </div>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $candidate->test_score }}
                                                    <span class="text-gray-500">points</span>
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex justify-center space-x-4">
                                            @if($candidate->test_completed_at && $candidate->tests->first())
                                                <a href="{{ route('admin.candidate-result', $candidate->id) }}" 
                                                class="text-blue-600 hover:text-blue-900">
                                                    View Results
                                                </a>
                                            @endif
                                            <form action="{{ route('candidate.approve', $candidate->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="text-green-600 hover:text-green-900">
                                                    Approve
                                                </button>
                                            </form>
                                            <form action="{{ route('candidate.reject', $candidate->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    Reject
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        No candidates found
                                    </td>
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