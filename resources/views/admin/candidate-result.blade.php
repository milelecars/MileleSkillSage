<!-- resources/views/admin/candidate-details.blade.php -->
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Main Card -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <!-- Header with basic info -->
                <div class="border-b border-gray-200 bg-white px-9 py-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-lg md:text-2xl font-bold text-gray-900">{{ $candidate->name }}</h1>
                            <p class="text-xs md:text-sm text-gray-600">{{ $candidate->email }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs md:text-sm text-gray-600">Registered on</p>
                            <p class="text-xs md:text-sm font-semibold">{{ $candidate->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="p-8">
                    <!-- Two Column Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Left Column - Test Details -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                <h3 class="text-base md:text-lg font-semibold text-gray-900">Test Details</h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <!-- Test Name -->
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div class="text-sm md:text-base">
                                        <p class="text-gray-600">Test Name</p>
                                        <p class="font-semibold">{{ $test->title }}</p>
                                    </div>
                                </div>

                                <!-- IP Address -->
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                        </svg>
                                    </div>
                                    <div class="text-sm md:text-base">
                                        <p class="text-gray-600">Location</p>
                                        <p class="font-semibold">
                                            {{ $location['formatted_address'] ?? 'N/A'}}
                                        </p>
                                    </div>
                                </div>

                                <!-- Started At -->
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div class="text-sm md:text-base">
                                        <p class="text-gray-600">Started At</p>
                                        <p class="font-semibold">{{ $test->pivot->started_at ? Carbon\Carbon::parse($test->pivot->started_at)->format('M d, Y H:i') : 'Not started' }}</p>
                                    </div>
                                </div>

                                <!-- Completed At -->
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div class="text-sm md:text-base">
                                        <p class="text-gray-600">Completed At</p>
                                        <p class="font-semibold">{{ $test->pivot->completed_at ? Carbon\Carbon::parse($test->pivot->completed_at)->format('M d, Y H:i') : 'Not completed' }}</p>
                                    </div>
                                </div>

                                <!-- Duration -->
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="text-sm md:text-base">
                                        <p class="text-gray-600">Duration</p>
                                        <p class="font-semibold">
                                            @if($test->pivot->started_at && $test->pivot->completed_at)
                                                {{ $duration }}
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                    </div>
                                </div>
                               
                                <!-- Report -->
                                <div>
                                    <a  class="flex items-center space-x-3" href="{{ route('reports.candidate-report', ['candidateId' => $candidate->id, 'testId' => $test->id]) }}">
                                        <div class="flex-shrink-0">
                                            <svg fill="#a7acb3" width="20px" height="20px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="#c5c9d0" stroke-width="0.00024000000000000003"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="m20 8-6-6H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM9 19H7v-9h2v9zm4 0h-2v-6h2v6zm4 0h-2v-3h2v3zM14 9h-1V4l5 5h-4z"></path></g></svg>
                                        </div>
                                        <div>
                                            <p class="text-sm md:text-base font-semibold">Report</p>
                                        </div>
                                    </a>
                                </div>

                                <!-- Suspension Reason -->
                                @if($test->pivot->is_suspended)
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                        </div>
                                        <div class="text-sm md:text-base">
                                            <p class="text-gray-600">Suspension Reason</p>
                                            <p class="font-semibold">
                                                {{ $suspensionReason }}
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Right Column - Status and Screenshots -->
                        <div class="space-y-8">
                            <!-- Test Status Card -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                    <h3 class="text-base md:text-lg font-semibold text-gray-900">Test Status</h3>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm md:text-base text-gray-600">Status</span>
                                        @if($test->pivot->status === 'accepted')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs md:text-sm font-medium text-green-800 bg-green-100">
                                                Accepted
                                            </span>
                                        @elseif($test->pivot->status === 'rejected')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs md:text-sm font-medium text-red-800 bg-red-100">
                                                Rejected
                                            </span>
                                        @elseif($test->pivot->status === 'completed')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs md:text-sm font-medium text-blue-800 bg-blue-100">
                                                Completed
                                            </span>
                                        @elseif($test->pivot->status === 'in_progress')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs md:text-sm font-medium text-yellow-800 bg-yellow-100">
                                                In Progress
                                            </span>
                                        @elseif($test->pivot->status === 'suspended')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs md:text-sm font-medium text-orange-800 bg-orange-100">
                                                Suspended
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs md:text-sm font-medium text-gray-800 bg-gray-100">
                                                Not Started
                                            </span>
                                        @endif
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-sm md:text-base text-gray-600">
                                                Score: {{ 
                                                    $hasMCQ 
                                                        ? ($test->pivot->correct_answers ?? '0' ) . ' / ' . ($totalQuestions ?? '')
                                                        : ($score ?? 'N/A') 
                                                }}
                                            </span>
                                            <span class="font-medium text-xs md:text-sm">
                                                {{ $hasMCQ ? $score . '%' : '' }}
                                            </span>

                                        </div>
                                        <div class="w-full px-4">
                                            <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                                <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300 ease-in-out"
                                                    style="width: {{ $score }}%"></div>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                            </div>

                            <!-- Screenshots Section -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                    <div class="flex items-center space-x-3">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <h3 class="text-base md:text-lg font-semibold text-gray-900">Monitoring</h3>
                                    </div>
                                </div>
                                
                                <div class="p-6">
                                    <div id="gallery" class="relative w-full" data-carousel="slide">
                                        <!-- Carousel wrapper -->
                                        <div class="relative h-72 overflow-hidden rounded-lg">
                                            @forelse($screenshots as $index => $screenshot)
                                                @php
                                                    $pathParts = explode('/', $screenshot['screenshot_path']);
                                                    $filename = end($pathParts);
                                                    $screenshotUrl = route('private.screenshot', [
                                                        'testId' => $test->id,
                                                        'candidateId' => $candidate->id,
                                                        'filename' => $screenshot['screenshot_path'],
                                                    ]);

                                                @endphp

                                                <div class="hidden duration-700 ease-in-out" data-carousel-item="{{ $index === 0 ? 'active' : '' }}">
                                                    <img 
                                                        src="{{ $screenshotUrl }}" 
                                                        class="rounded-lg absolute block max-w-full h-auto -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2"
                                                        alt="Screenshot {{ $loop->iteration }}"
                                                    >
                                                    <!-- Timestamp overlay -->
                                                    <div class="absolute bottom-0 left-0 right-0 p-4 bg-black/60 backdrop-blur-sm">
                                                        <div class="flex justify-between items-center">
                                                            <span class="text-white text-xs md:text-sm">Screenshot #{{ $loop->iteration }}</span>
                                                            <span class="text-white text-xs md:text-sm">
                                                                {{ \Carbon\Carbon::parse($screenshot['created_at'])->format('M d, Y H:i:s') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="h-full flex items-center justify-center bg-gray-50">
                                                    <div class="text-center">
                                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                        <p class="text-xs md:text-base mt-2 text-gray-500">No screenshots available</p>
                                                    </div>
                                                </div>
                                            @endforelse
                                        </div>

                                        <!-- Slider controls -->
                                        @if($screenshots->count() > 1)
                                            <button type="button" class="absolute top-0 start-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none" data-carousel-prev>
                                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 group-hover:bg-white/50 group-focus:ring-4 group-focus:ring-white group-focus:outline-none">
                                                    <svg class="w-4 h-4 text-white rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 1 1 5l4 4"/>
                                                    </svg>
                                                </span>
                                            </button>
                                            <button type="button" class="absolute top-0 end-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none" data-carousel-next>
                                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 group-hover:bg-white/50 group-focus:ring-4 group-focus:ring-white group-focus:outline-none">
                                                    <svg class="w-4 h-4 text-white rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                                                    </svg>
                                                </span>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <!-- @if($test->pivot->status !== 'accepted' && $test->pivot->status !== 'rejected')
                        <div class="mt-8 flex justify-end space-x-4">
                            <form action="{{ route('candidate.accept', $candidate) }}" method="POST" class="inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="test_id" value="{{ $test->id }}">
                                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-xs md:text-sm md:text-base font-medium rounded-lg text-white bg-green-600 hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Accept
                                </button>
                            </form>
                            <form action="{{ route('candidate.reject', $candidate) }}" method="POST" class="inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="test_id" value="{{ $test->id }}">
                                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-xs md:text-sm md:text-base font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Reject
                                </button>
                            </form>                                
                        </div>
                    @endif -->
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 