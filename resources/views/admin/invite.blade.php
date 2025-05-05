<x-app-layout>
    <div class="py-12 text-theme bg-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 pl-6 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-500 text-red-700 rounded-lg">
                    <ul class="pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="p-4 sm:p-6 md:p-8">
                    {{-- Header Section --}}
                    <div class="mb-8">
                        <h1 class="text-lg md:text-2xl font-extrabold text-gray-900 mb-2">Invite Candidates</h1>
                        <p class="text-sm md:text-base text-gray-600">Select a candidate and choose tests to send invitations.</p>
                    </div>

                    {{-- Candidate Selection Form --}}
                    <form action="{{ route('admin.select-candidate') }}" method="GET" class="mb-8">
                        <div class="mb-6">
                            <label for="candidate" class="text-base md:text-lg font-semibold text-gray-800">Select Candidate</label>

                            {{-- Search by Email --}}
                            <select 
                                name="selected_email" 
                                class="text-sm md:text-base w-full border border-gray-300 rounded-md shadow-sm p-2.5 mt-4 focus:ring-blue-500 focus:border-blue-500" 
                                onchange="this.form.submit()"
                            >
                                <option value="">Search by email...</option>
                                @foreach($emailToTestIds as $email => $testIds)
                                    <option value="{{ $email }}" {{ request('selected_email') == $email ? 'selected' : '' }}>
                                        {{ $email }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- <div class="mb-6">
                            {{-- Search by Name --}}
                            <label for="candidate_name" class="text-sm md:text-base md:text-lg font-semibold text-gray-800">Search by Name</label>
                            <input 
                                type="text" 
                                name="candidate_name" 
                                value="{{ request('candidate_name') }}" 
                                class="w-full border border-gray-300 rounded-md shadow-sm p-2.5 mt-4 focus:ring-blue-500 focus:border-blue-500" 
                                placeholder="Enter candidate name..."
                                oninput="this.form.submit()"
                            >
                        </div> -->
                    </form>


                    @if(request('selected_email'))
                        <form action="{{ route('admin.send') }}" method="POST">
                            @csrf
                            <input type="hidden" name="email_test_map[{{ request('selected_email') }}]" value="">

                            {{-- Already Invited Tests Section --}}
                            <div class="mb-8">
                                <h4 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Already Invited Tests</h4>
                                <div class="bg-gray-50 rounded-lg p-6">
                                    <div class="space-y-3">
                                        @forelse($emailToTestIds[request('selected_email')] ?? [] as $testId)
                                            <div class="text-sm md:text-base flex items-center text-gray-700 bg-white p-3 rounded-md shadow-sm">
                                                <svg class="h-5 w-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                {{ $tests->find($testId) ? $tests->find($testId)->title : '' }}
                                            </div>
                                        @empty
                                            <p class="text-gray-500 italic">No previous invitations</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            {{-- Role Input Field --}}
                            <div class="mb-6">
                                <label for="role" class="text-base md:text-lg font-semibold text-gray-800">Enter Role</label>
                                <input 
                                    type="text" 
                                    name="role" 
                                    id="role" 
                                    class="placeholder:text-sm text-sm md:text-base w-full border border-gray-300 rounded-md shadow-sm p-2.5 mt-2 focus:ring-blue-500 focus:border-blue-500" 
                                    placeholder="Enter candidate's role..." 
                                    required
                                >
                            </div>
                            
                            {{-- Department Dropdown --}}
                            <div class="mb-6">
                                <label for="department" class="text-base md:text-lg font-semibold text-gray-800">Select or Create Department</label>
                                <select 
                                    id="department" 
                                    name="department" 
                                    class="department-select placeholder:text-sm text-sm md:text-base w-full border border-gray-300 rounded-md shadow-sm p-2.5 mt-2">
                                </select>
                            </div>

                            {{-- Available Tests Section --}}
                            <div class="mb-8">
                                <h4 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Select Tests to Invite</h4>
                                <div class="bg-gray-50 rounded-lg p-6">
                                    <div class="space-y-3">
                                        @forelse($emailToUninvitedTestIds[request('selected_email')] ?? [] as $testId)
                                            <div class="flex items-center bg-white p-3 rounded-md shadow-sm hover:bg-gray-50 transition-colors">
                                                <input 
                                                    type="checkbox" 
                                                    id="test_{{ $testId }}" 
                                                    name="email_test_map[{{ request('selected_email') }}][]" 
                                                    value="{{ $testId }}"
                                                    class="h-4 w-4 text-blue-700 border-gray-300 rounded-lg "
                                                >
                                                <label 
                                                    for="test_{{ $testId }}" 
                                                    class="text-sm md:text-base ml-3 block text-gray-700 cursor-pointer flex-1"
                                                >
                                                    {{ $tests->find($testId) ? $tests->find($testId)->title : '' }}
                                                </label>
                                            </div>
                                        @empty
                                            <p class="text-gray-500 italic">No available tests to invite</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            {{-- Submit Button --}}
                            @if(!empty($emailToUninvitedTestIds[request('selected_email')]))
                                <div class="flex justify-end">
                                    <button type="submit" class="text-xs inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white  tracking-widest hover:bg-blue-700 disabled:opacity-25 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 md:w-5 md:h-5 mr-2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                                        </svg>
                                        Send
                                    </button>
                                </div>
                            @endif
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
    $(document).ready(function() {
        $('.department-select').select2({
            placeholder: "Select or create a department",
            tags: true, // Allow new entries
            minimumInputLength: 2,
            ajax: {
                url: '{{ route('departments.search') }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return {
                        results: data.map(item => ({
                            id: item.name, // send name to server
                            text: item.name
                        }))
                    };
                },
                cache: true
            },
            createTag: function (params) {
                var term = $.trim(params.term);
                return {
                    id: term,
                    text: term,
                    newTag: true // add additional flag
                };
            }
        });
    });
</script>