<x-app-layout>
    <div class="py-12 text-theme">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-lg md:text-2xl font-bold mb-6">Create a New Test</h1>

            <form action="{{ route('tests.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-white shadow-md rounded-lg p-4 sm:p-6 md:p-8">
                    
                    <div>
                        <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Test Title</label>
                        <input type="text" name="title" id="title" required
                            class="placeholder:text-sm text-sm md:text-base appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            placeholder="Enter the test title" value="{{ old('title') }}">
                    </div>

                    <div>
                        <label for="duration" class="block text-gray-700 text-sm font-bold mb-2">Duration (in minutes)</label>
                        <input type="number" name="duration" id="duration" required
                            class="placeholder:text-sm text-sm md:text-base appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            placeholder="Enter the duration" value="{{ old('duration') }}">
                    </div>

                    <div wire:ignore.self>
                        <label for="invitation_link" class="block text-gray-700 text-sm font-bold mb-2">Invitation Link</label>
                        @livewire('invitation-generator')
                    </div>

                    <div class="col-span-1 md:col-span-3">
                        <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description</label>
                        <textarea name="description" id="description" rows="4"
                            class="placeholder:text-sm text-sm md:text-base appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            placeholder="Enter a description for the test">{{ old('description') }}</textarea>
                    </div>

                    <div class="col-span-1 md:col-span-3">
                        <label for="file" class="text-sm md:text-base block text-gray-700 font-bold mb-2">Import Questions</label>
                        <input type="file" name="file" accept=".xlsx,.csv" required
                            class="placeholder:text-sm text-sm md:text-base rounded-lg border border-neutral-500 w-full">
                        <p class="mt-2 text-xs md:text-sm text-gray-600">
                            Please ensure your Excel file follows the required
                            <a href="https://milelemotors-my.sharepoint.com/:x:/g/personal/helia_haghighi_milele_com/EQWDcWUxRxZEvVYBTZ0KvQgB41V2mIJ8uRO_99c2pRg4Mg?e=MzqcJw"
                                class="text-blue-600 hover:text-blue-800 underline" target="_blank">
                                template
                            </a>
                            format.
                        </p>
                    </div>

                    <div class="flex items-center justify-center col-span-1 md:col-span-3 mt-5">
                        <button type="submit"
                            class="text-sm md:text-base text-white bg-blue-700 hover:bg-blue-600 font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline">
                            Create Test
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</x-app-layout>
