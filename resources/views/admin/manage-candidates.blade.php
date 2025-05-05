<x-app-layout>
    <div class="py-12 text-theme">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg ">
                <div class="p-6">
                    
                    <div class="flex justify-between md:items-center mb-3 md:mb-6">
                        <h1 class="text-xl md:text-2xl font-bold text-gray-900 mb-6 md:mb-0">Manage Candidates</h1>
                        
                        <!-- search functionality  -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center justify-center rounded-lg p-1 md:mt-1 bg-blue-600 focus:outline-none" title="More options">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" class="w-5 h-5 md:w-7 md:h-7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z" />
                                </svg>
                            </button>

                            <!-- Dropdown filter menu -->
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-72 bg-white border border-gray-200 shadow-lg rounded-lg p-4 z-50">
                                <form method="GET" action="{{ route('admin.manage-candidates') }}" class="space-y-3">
                                    <div>
                                        <label for="test_filter" class="block text-xs font-medium text-gray-700">Test</label>
                                        <select name="test_filter" id="test_filter" class="w-full border-gray-300 rounded-md mt-1 placeholder:text-xs md:placeholder:text-sm">
                                            <option value="" class="placeholder:text-xs md:placeholder:text-sm">All Tests</option>
                                            @foreach($availableTests as $test)
                                                <option value="{{ $test->id }}" {{ $testFilter == $test->id ? 'selected' : '' }}>{{ $test->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Name Filter -->
                                    <div>
                                        <label for="name" class="block text-xs font-medium text-gray-700">Name</label>
                                        <input type="text" name="name" id="name" value="{{ request('name') }}" placeholder="Search by name" class="placeholder:text-xs md:placeholder:text-sm w-full border-gray-300 rounded-md mt-1">
                                    </div>

                                    <!-- Email Filter -->
                                    <div>
                                        <label for="email" class="block text-xs font-medium text-gray-700">Email</label>
                                        <input type="text" name="email" id="email" value="{{ request('email') }}" placeholder="Search by email" class="placeholder:text-xs md:placeholder:text-sm w-full border-gray-300 rounded-md mt-1">
                                    </div>

                                    <!-- Role Filter -->
                                    <div>
                                        <label for="role" class="block text-xs font-medium text-gray-700">Role</label>
                                        <input type="text" name="role" id="role" value="{{ request('role') }}" placeholder="Search by role" class="placeholder:text-xs md:placeholder:text-sm w-full border-gray-300 rounded-md mt-1">
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex justify-between items-center pt-5">
                                        <a href="{{ route('admin.export-candidates', ['search' => $search ?? '', 'test_filter' => $testFilter ?? '']) }}" class="inline-flex items-center bg-green-600 text-white text-xs md:text-sm px-3 py-2 rounded-md hover:bg-green-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Export
                                        </a>

                                        <button type="submit" class="bg-blue-600 text-white px-3 py-2 rounded-md text-xs md:text-sm">Search</button>

                                        <a href="{{ route('admin.manage-candidates') }}" class="bg-gray-600 text-white px-3 py-2 rounded-md text-xs md:text-sm">Clear</a>

                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-2 text-center items-center md:grid-cols-4 gap-4 mb-6 relative">
                        <div class="bg-blue-50 p-4 rounded-lg gap-2 h-full flex flex-col justify-between">
                            <h3 class="text-base md:text-lg font-semibold text-blue-700">Active</h3>
                            <p class="text-lg md:text-2xl font-bold text-blue-900">{{ $activeTests }}</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg gap-2 h-full flex flex-col justify-between">
                            <h3 class="text-base md:text-lg font-semibold text-green-700">Invited</h3>
                            <p class="text-lg md:text-2xl font-bold text-green-900">{{ $totalInvited }}</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg gap-2 h-full flex flex-col justify-between">
                            <h3 class="text-base md:text-lg font-semibold text-purple-700">Completed</h3>
                            <p class="text-lg md:text-2xl font-bold text-purple-900">{{ $completedTestsCount }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg gap-2 h-full flex flex-col justify-between">
                            <h3 class="text-base md:text-lg font-semibold text-gray-700">Tests</h3>
                            <p class="text-lg md:text-2xl font-bold text-gray-900">{{ $totalTests }}</p>
                        </div>
                    </div>
                    
                    @if(session('error'))
                        <div class="mb-4 text-sm text-red-600 bg-red-100 border border-red-400 p-3 rounded-md">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="mb-4 text-sm text-green-600 bg-green-100 border border-green-400 p-3 rounded-md">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-4 text-sm text-red-600 bg-red-100 border border-red-400 p-3 rounded-md">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="overflow-x-auto rounded-lg ">
                        <div class="max-h-[70vh] overflow-y-auto border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200 " id="candidatesTable">
                                <thead class="bg-gray-100 sticky top-0 z-10">
                                    <tr>
                                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase cursor-pointer" data-sort="candidate">
                                            <div class="flex items-center justify-center">
                                                Candidate
                                                <button class="sort-icon ml-1">⇅</button>
                                            </div>
                                        </th>
                                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase cursor-pointer" data-sort="test">
                                            <div class="flex items-center justify-center">
                                                Test
                                                <button class="sort-icon ml-1">⇅</button>
                                            </div>
                                        </th>
                                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase cursor-pointer" data-sort="role">
                                            <div class="flex items-center justify-center">
                                                Role
                                                <button class="sort-icon ml-1">⇅</button>
                                            </div>
                                        </th>
                                        <th class="px-8 py-3 text-xs font-semibold text-gray-500 uppercase cursor-pointer" data-sort="status">
                                            <div class="flex items-center justify-center">
                                                Status
                                                <button class="sort-icon ml-1">⇅</button>
                                            </div>
                                        </th>
                                        <th class="py-3 text-xs font-semibold text-gray-500 uppercase cursor-pointer w-[10%]" data-sort="started">
                                            <div class="px-2 flex items-center">
                                                Started At
                                                <button class="sort-icon ml-1">⇅</button>
                                            </div>
                                        </th>
                                        <th class="py-3 text-xs font-semibold text-gray-500 uppercase cursor-pointer w-[11%]" data-sort="completed">
                                            <div class="px-2 flex items-center">
                                                Completed At
                                                <button class="sort-icon ml-1">⇅</button>
                                            </div>
                                        </th>
                                        <th class="px-3 py-3 text-xs font-semibold text-gray-500 uppercase cursor-pointer" data-sort="score">
                                            <div class="flex items-center justify-center">
                                                Score
                                                <button class="sort-icon ml-1">⇅</button>
                                            </div>
                                        </th>
                                        <th class="px-3 py-3 text-xs font-semibold text-gray-500 uppercase cursor-pointer" data-sort="percentile">
                                            <div class="flex items-center justify-center">
                                                Percentile
                                                <button class="sort-icon ml-1">⇅</button>
                                            </div>
                                        </th>
                                        <th class="px-3 py-3 text-xs font-semibold text-gray-500 uppercase">Report</th>
                                        <th class="px-3 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 text-center">
                                    @forelse($candidates as $candidate)
                                        <tr class="relative items-center justify-center" 
                                            data-candidate="{{ $candidate['name'] ?? $candidate['email'] }}" 
                                            data-test="{{ $candidate['test_title'] }}" 
                                            data-role="{{ $candidate['role'] ?? '-' }}"
                                            data-status="{{ $candidate['status'] }}"
                                            data-started="{{ isset($candidate['started_at']) ? \Carbon\Carbon::parse($candidate['started_at'])->format('Y-m-d H:i:s') : '0' }}"
                                            data-completed="{{ isset($candidate['completed_at']) ? \Carbon\Carbon::parse($candidate['completed_at'])->format('Y-m-d H:i:s') : '0' }}"
                                            data-score="{{ isset($candidate['score']) ? $candidate['score'] : '0' }}"
                                            data-percentile="{{ isset($candidate['percentile']) ? $candidate['percentile'] : '0' }}">
                                            <td class="px-2 py-4 h-full">
                                                @if($candidate['has_started'])
                                                    <a href="{{ route('admin.candidate-result', ['test' => $candidate['test_id'], 'candidate' => $candidate['id']]) }}" class="hover:text-blue-600">
                                                        <div class="text">{{ $candidate['name'] }}</div>
                                                        <div class="text-xs text-gray-500">{{ $candidate['email'] }}</div>
                                                    </a>
                                                @else
                                                    <div class="text-xs text-gray-500">{{ $candidate['email'] }}</div>
                                                @endif
                                            </td>

                                            <td class="px-2 py-4 h-full text-xs md:text-sm">{{ $candidate['test_title'] }}</td>

                                            <td class="px-2 py-4 h-full text-xs md:text-sm">{{ $candidate['role'] ?? "-" }}</td>

                                            
                                            <td class="px-2 py-4 h-full text-xs md:text-sm">
                                                @if($candidate['status'] === 'accepted')
                                                    <span class="text-green-800 bg-green-100 px-2 py-1 rounded-full">Accepted</span>
                                                @elseif($candidate['status'] === 'rejected')
                                                    <span class="text-red-800 bg-red-100 px-2 py-1 rounded-full">Rejected</span>
                                                @elseif($candidate['status'] === 'completed')
                                                    <span class="text-blue-800 bg-blue-100 px-2 py-1 rounded-full">Completed</span>
                                                @elseif($candidate['status'] === 'in_progress')
                                                    <span class="text-yellow-800 bg-yellow-100 px-2 py-1 rounded-full">In Progress</span>
                                                @elseif($candidate['status'] === 'suspended')
                                                    <span class="text-orange-800 bg-orange-100 px-2 py-1 rounded-full">Suspended</span>
                                                @elseif($candidate['status'] === 'expired')
                                                    <span class="text-red-800 bg-red-100 px-2 py-1 rounded-full">Expired</span>
                                                @else
                                                    <span class="text-gray-800 bg-gray-100 px-2 py-1 rounded-full">Not Started</span>
                                                @endif
                                            </td>
                                            
                                            <td class="py-4 h-full text-xs">
                                                {{ isset($candidate['started_at']) ? \Carbon\Carbon::parse($candidate['started_at'])->format('M d, Y H:i') : '-' }}
                                            </td>
                                            <td class="py-4 h-full text-xs">
                                                {{ isset($candidate['completed_at']) ? \Carbon\Carbon::parse($candidate['completed_at'])->format('M d, Y H:i') : '-' }}
                                            </td>
                                            <td class="px-2 py-4 h-full text-xs md:text-sm">
                                                @if(isset($candidate['score']))
                                                    <span class="font-medium">
                                                    {{$candidate['score']}}{{ $candidate['hasMCQ'] ? '%' : '' }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            
                                            <td class="px-2 py-4 h-full text-xs md:text-sm">
                                                @if (isset($candidate['percentile']))
                                                    @if ($candidate['percentile'] >= 99)
                                                        Top 1%
                                                    @elseif ($candidate['percentile'] > 0)
                                                        Top {{ 100 - floor($candidate['percentile']) }}%
                                                    @else
                                                        Bottom Performer
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            
                                            <td class="py-6 h-full">
                                                @if(isset($candidate['completed_at']))
                                                    <a class="flex items-center justify-center" href="{{ route('reports.candidate-report', ['candidateId' => $candidate['id'], 'testId' => $candidate['test_id']]) }}">
                                                        <svg fill="#102141" width="25px" height="25px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="#102141" stroke-width="0.00024000000000000003"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="m20 8-6-6H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM9 19H7v-9h2v9zm4 0h-2v-6h2v6zm4 0h-2v-3h2v3zM14 9h-1V4l5 5h-4z"></path></g></svg>
                                                    </a>
                                                @else
                                                    <span>-</span>
                                                @endif
                                            </td>

                                            <td class="py-6 h-full">
                                                <div class="relative flex items-center justify-center" x-data="{ open: false, showDeadlineModal: false }">
                                                    @if($candidate['status'] === 'completed' || ($candidate['status'] === 'suspended' && $candidate['unsuspend_count'] < 1))
                                                        <button @click="open = !open" class="text-gray-600 hover:text-gray-800">
                                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                                            </svg>
                                                        </button>
                                                        
                                                        <div x-show="open" 
                                                            @click.away="open = false" 
                                                            class="absolute right-0 mt-2 w-36 bg-white rounded-md shadow-lg py-1 z-50">
                                                            
                                                            @if($candidate['status'] === 'suspended' && $candidate['unsuspend_count'] < 1)
                                                                <form action="{{ route('admin.unsuspend-test', [$candidate['id'], $candidate['test_id']]) }}" method="POST" class="block"
                                                                    onsubmit="return confirm('Are you sure you want to unsuspend this test?');">
                                                                    @csrf
                                                                    <button type="submit" class="w-full text-left px-4 py-2 text-xs md:text-sm text-orange-600 hover:bg-gray-100">
                                                                        Unsuspend Test
                                                                    </button>
                                                                </form>
                                                            @endif

                                                            @if($candidate['status'] === 'completed')
                                                                <form action="{{ route('candidate.accept', $candidate['id']) }}" method="POST" class="block"
                                                                    onsubmit="return confirm('Are you sure you want to accept this candidate?');">
                                                                    @csrf @method('PUT')
                                                                    <input type="hidden" name="test_id" value="{{ $candidate['test_id'] }}">
                                                                    <button type="submit" class="w-full text-left px-4 py-2 text-xs md:text-sm text-green-700 hover:bg-gray-100">
                                                                        Accept
                                                                    </button>
                                                                </form>

                                                                <form action="{{ route('candidate.reject', $candidate['id']) }}" method="POST" class="block"
                                                                    onsubmit="return confirm('Are you sure you want to reject this candidate?');">
                                                                    @csrf @method('PUT')
                                                                    <input type="hidden" name="test_id" value="{{ $candidate['test_id'] }}">
                                                                    <button type="submit" class="w-full text-left px-4 py-2 text-xs md:text-sm text-orange-600 hover:bg-gray-100">
                                                                        Reject
                                                                    </button>
                                                                </form>

                                                                <form action="{{ route('candidate.delete', [$candidate['id'], $candidate['test_id']]) }}" method="POST" class="block"
                                                                    onsubmit="return confirm('Are you sure you want to delete this candidate?');">
                                                                    @csrf @method('DELETE')
                                                                    <button type="submit" class="w-full text-left px-4 py-2 text-xs md:text-sm text-red-600 hover:bg-gray-100">
                                                                        Delete
                                                                    </button>
                                                                </form>
                                                            @endif

                                                        </div>
                                                    @elseif($candidate['status'] === 'expired')
                                                        <button @click="showDeadlineModal = true" class="text-blue-600 hover:text-blue-800 text-xs md:text-sm">
                                                            Extend Deadline
                                                        </button>

                                                        <!-- Deadline Extension Modal -->
                                                        <div x-show="showDeadlineModal" 
                                                            class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center">
                                                            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                                                                <h3 class="text-base md:text-lg font-semibold mb-6">Extend Deadline</h3>

                                                                <div class="flex gap-2 mb-4">
                                                                    <span class="text-gray-600">Current Deadline:</span>
                                                                    <span class="block font-medium">{{ \Carbon\Carbon::parse($candidate['expiration_date'])->format('M d, Y H:i') }}</span>
                                                                </div>
                                                                
                                                                <form action="{{ route('invitations.extend-deadline') }}" method="POST" onsubmit="return validateDeadline()">
                                                                    @csrf
                                                                    <input type="hidden" name="test_id" value="{{ $candidate['test_id'] }}">
                                                                    <input type="hidden" name="email" value="{{ $candidate['email'] }}">
                                                                    
                                                                    <div class="flex gap-2 items-center mb-4">
                                                                        <label class="text-gray-600">New Deadline:</label>
                                                                        <input type="datetime-local" 
                                                                            id="new_deadline"
                                                                            name="new_deadline" 
                                                                            class="border border-gray-300 rounded-md p-2"
                                                                            min="{{ now()->format('Y-m-d\TH:i') }}"
                                                                            required>
                                                                        <span id="error-message" class="text-red-600 text-xs md:text-sm hidden">Date and time are required.</span>
                                                                    </div>
                                                                    
                                                                    <div class="flex justify-end space-x-3">
                                                                        <button type="button" @click="showDeadlineModal = false" class="px-4 py-2 text-xs md:text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                                                                            Cancel
                                                                        </button>
                                                                        <button type="submit" class="px-4 py-2 text-xs md:text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
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
                                            <td colspan="9" class="text-center py-4">No candidates found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<style>
    .sort-icons svg.active-asc {
        color: #2563eb;
    }
    .sort-icons svg.active-desc {
        color: #2563eb;
    }
    .sort-icons svg {
        color: #9ca3af;
    }
    
    th.sorted-asc {
        background-color: #dbeafe; 
        color: #1e40af !important; 
    }
    th.sorted-desc {
        background-color: #dbeafe; 
        color: #1e40af !important; 
    }
    
    th.sorted-asc .sort-icon,
    th.sorted-desc .sort-icon {
        color: #2563eb; 
    }
</style>


<script>
    function validateDeadline() {
        const deadlineInput = document.getElementById('new_deadline').value;
        const errorMessage = document.getElementById('error-message');

        if (!deadlineInput) {
            errorMessage.classList.remove('hidden');
            return false;
        }
        
        errorMessage.classList.add('hidden');
        return true;
    }

  
    document.addEventListener('DOMContentLoaded', function () {
        const table = document.getElementById('candidatesTable');
        const headers = table.querySelectorAll('th[data-sort]');
        let sortDirection = {};
        let currentSortedHeader = null;

        headers.forEach(header => {
            header.addEventListener('click', function () {
                const sortKey = this.getAttribute('data-sort');
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));

                // Flip sort direction on each click
                sortDirection[sortKey] = !sortDirection[sortKey];

                rows.sort((a, b) => {
                    let aValue = a.getAttribute(`data-${sortKey}`) || '';
                    let bValue = b.getAttribute(`data-${sortKey}`) || '';

                    if (sortKey === 'score' || sortKey === 'percentile') {
                        aValue = parseFloat(aValue) || 0;
                        bValue = parseFloat(bValue) || 0;

                        if (aValue < bValue) return sortDirection[sortKey] ? 1 : -1;
                        if (aValue > bValue) return sortDirection[sortKey] ? -1 : 1;

                        return 0;
                    }
                    else if (sortKey === 'started' || sortKey === 'completed') {
                        aValue = new Date(aValue).getTime() || 0;
                        bValue = new Date(bValue).getTime() || 0;
                        
                        if (aValue < bValue) return sortDirection[sortKey] ? 1 : -1;
                        if (aValue > bValue) return sortDirection[sortKey] ? -1 : 1;

                        return 0;
                    }
                    else {
                        aValue = aValue.toString().toLowerCase();
                        bValue = bValue.toString().toLowerCase();

                        const aIsEmpty = aValue === '-' || aValue.trim() === '';
                        const bIsEmpty = bValue === '-' || bValue.trim() === '';

                        if (aIsEmpty && !bIsEmpty) return 1;  // push a down
                        if (!aIsEmpty && bIsEmpty) return -1; // push b down
                        if (aIsEmpty && bIsEmpty) return 0;   // both empty → equal

                        // Normal alphabetical comparison
                        if (aValue < bValue) return sortDirection[sortKey] ? -1 : 1;
                        if (aValue > bValue) return sortDirection[sortKey] ? 1 : -1;
                        return 0;
                    }



                    if (aValue < bValue) return sortDirection[sortKey] ? -1 : 1;
                    if (aValue > bValue) return sortDirection[sortKey] ? 1 : -1;
                    return 0;
                });

                tbody.innerHTML = '';
                rows.forEach(row => tbody.appendChild(row));

                // Reset all headers
                updateSortIcons();
                resetHeaderColors();
                
                // Update the current header
                this.querySelector('.sort-icon').innerText = sortDirection[sortKey] ? '↑' : '↓';
                
                // Apply the appropriate class based on sort direction
                if (sortDirection[sortKey]) {
                    this.classList.add('sorted-asc');
                } else {
                    this.classList.add('sorted-desc');
                }
                
                currentSortedHeader = this;
            });
        });

        function updateSortIcons() {
            headers.forEach(h => {
                const icon = h.querySelector('.sort-icon');
                if (icon) {
                    icon.innerText = '⇅'; // reset others
                }
            });
        }
        
        function resetHeaderColors() {
            headers.forEach(h => {
                h.classList.remove('sorted-asc', 'sorted-desc');
            });
        }
    });

</script>