<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-bold mb-6">Edit Test</h1>
                    <form action="{{ route('tests.update', $test->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Test Name:</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $test->name) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        </div>
                        <div class="mb-4">
                            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
                            <textarea name="description" id="description" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">{{ old('description', $test->description) }}</textarea>
                        </div>
                        <div class="mb-4">
                            <label for="file" class="block text-gray-700 text-md font-bold mb-2">Import Questions (Optional)</label>
                            <input type="file" name="file" accept=".xlsx,.csv,.json">
                        </div>
                        <div class="flex items-center justify-between">
                            <a href="{{ url()->previous() == route('tests.index') ? route('tests.index') : route('tests.show', $test->id) }}"
                               class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Update Test
                            </button>
                        </div>
                    </form>                  
                </div>
            </div>
        </div>
    </div>
</x-app-layout>