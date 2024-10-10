<x-app-layout>
    <div class="py-12 text-theme">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold mb-6">Create a New Test</h1>
            <form action="{{ route('tests.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-2 gap-6 bg-white shadow-md rounded p-8">
                    <div class="mr-4">
                        <label for="name" class="block text-gray-700 text-md font-bold mb-2">Test Name</label>
                        <input type="text" name="name" id="name" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Enter the test name" value="{{ old('name') }}">
                    </div>

                    <div class="ml-4">
                        <label for="invitation_link" class="block text-gray-700 text-md font-bold mb-2">Invitation Link</label>
                        <div class="flex">
                            <input type="text" name="invitation_link" id="invitation_link" readonly class="shadow appearance-none border border-r-0 rounded-l w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="{{ old('invitation_link') }}">
                            <button type="submit" name="generate_link" value="1" class="text-slate-900 bg-slate-300 hover:bg-slate-200 border border-neutral-500 font-bold px-3 rounded-r shadow focus:outline-none focus:shadow-outline text-xs">
                                <pre>Generate Link</pre>
                            </button>
                        </div>
                    </div>

                    <div class="col-span-2">
                        <label for="description" class="block text-gray-700 text-md font-bold mb-2">Description</label>
                        <textarea name="description" id="description" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Enter a description for the test">{{ old('description') }}</textarea>
                    </div>

                    <div class="flex items-center justify-center col-span-2 mt-5">
                        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-600 font-bold py-2 px-4 rounded text-center focus:outline-none focus:shadow-outline">Create Test</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>