<x-app-layout>
    <div class="py-12 text-theme">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold mb-6">Create a New Test</h1>
            <form action="{{ route('tests.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-3 gap-6 bg-white shadow-md rounded p-8">
                    <div class="mr-4">
                        <label for="name" class="block text-gray-700 text-md font-bold mb-2">Test Name</label>
                        <input type="text" name="name" id="name" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Enter the test name" value="{{ old('name') }}">
                    </div>
                    <div class="mr-4">
                        <label for="duration" class="block text-gray-700 text-md font-bold mb-2">Duration</label>
                        <input type="text" name="duration" id="duration" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Enter the duration" value="{{ old('duration') }}">
                    </div>
                    <div class="ml-4" wire:ignore.self>
                        <label for="invitation_link" class="block text-gray-700 text-md font-bold mb-2">Invitation Link</label>
                       
                        @livewire('invitation-generator')
                    </div>
                    <div class="col-span-3">
                        <label for="description" class="block text-gray-700 text-md font-bold mb-2">Description</label>
                        <textarea name="description" id="description" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Enter a description for the test">{{ old('description') }}</textarea>
                    </div>
                    <div class="col-span-3">
                        <label for="file" class="block text-gray-700 text-md font-bold mb-2">Import Questions</label>
                        <input type="file" name="file" accept=".xlsx,.csv" required>
                    </div>
                    <div class="flex items-center justify-center col-span-3 mt-5">
                        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-600 font-bold py-2 px-4 rounded text-center focus:outline-none focus:shadow-outline">Create Test</button>
                    </div>
                </div>
            </form>
                       
        </div>
    </div>
</x-app-layout>