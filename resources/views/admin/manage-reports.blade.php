<x-app-layout>
    <div class="py-12 text-theme">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="p-6">
                    <h1 class="text-3xl font-bold text-gray-900 mb-6">Manage Reports</h1>

                    @if(session('success'))
                        <div class="bg-amber-50 border-l-4 border-amber-200 text-amber-800 p-4 mb-4 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-50 border-l-4 border-red-200 text-red-800 p-4 mb-4 rounded-lg">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-blue-700">Total Tests</h3>
                            <p class="text-2xl font-bold text-blue-900">{{ $totalTests }}</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-green-700">Total Reports</h3>
                            <p class="text-2xl font-bold text-green-900">{{ $totalReports }}</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-purple-700">Total Candidates</h3>
                            <p class="text-2xl font-bold text-purple-900">{{ $totalCandidates }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-700">Completed Tests</h3>
                            <p class="text-2xl font-bold text-gray-900">{{ $completedTests }}</p>
                        </div>
                    </div>

                    <!-- Reports Table -->
                    <div class="overflow-x-auto rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 border border-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Test ID</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Test Title</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Description</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Total Candidates</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Total Reports</th>
                                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($testReports as $report)
                                    <tr class="text-center">
                                        <td class="px-2 py-4 text-sm">{{ $report->id }}</td>
                                        <td class="px-2 py-4 text-sm">{{ $report->title }}</td>
                                        <td class="px-2 py-4 text-sm">{{ Str::limit($report->description, 50) }}</td>
                                        <td class="px-2 py-4 text-sm">{{ $report->total_candidates }}</td>
                                        <td class="px-2 py-4 text-sm">{{ $report->total_reports }}</td>
                                        <td class="px-2 py-4">
                                            <div class="flex justify-center">
                                            <a href="{{ route('admin.download-test-reports', $report->id) }}" 
                                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                                Download All
                                            </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                            No reports found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>