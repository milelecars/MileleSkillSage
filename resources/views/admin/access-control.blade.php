<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg p-4 sm:p-5 text-theme">
                <h1 class="text-lg md:text-2xl font-bold mb-6 ml-5 md:ml-0">Manage Admins</h1>

                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 text-sm p-4 mb-4 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 text-sm p-4 mb-4 rounded-lg">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="overflow-x-auto rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                        <thead class="bg-gray-50">
                            <tr class="text-xs md:text-sm text-gray-500 uppercase">
                                <th class="px-2 py-3 w-1/4 text-center">Name</th>
                                <th class="px-2 py-3 w-1/4 text-center">Email</th>
                                <th class="px-2 py-3 w-1/4 text-center">Created At</th>
                                <th class="px-2 py-3 w-1/4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 text-center">
                            @foreach($admins as $admin)
                                <tr>
                                    <td class="px-2 py-4 text-xs md:text-sm">
                                        <form method="POST" action="{{ route('admin.access-control.update', $admin->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <input name="name" type="text" value="{{ $admin->name }}" class="border border-gray-300 rounded-md p-1 text-sm w-full" />
                                    </td>
                                    <td class="px-2 py-4 text-xs md:text-sm">
                                            <input name="email" type="email" value="{{ $admin->email }}" class="border border-gray-300 rounded-md p-1 text-sm w-full" />
                                    </td>
                                    <td class="px-2 py-4 text-xs md:text-sm">
                                            {{ $admin->created_at->format('Y-m-d') }}
                                    </td>
                                    <td class="px-2 py-4 flex justify-center items-center space-x-2">
                                            <button type="submit" title="Update Admin" class="text-yellow-500 hover:text-yellow-600 p-2 rounded-lg hover:bg-yellow-100 transition duration-150 ease-in-out">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 md:w-6 md:h-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                </svg>
                                            </button>
                                        </form>

                                        
                                        <form action="{{ route('admin.access-control.destroy', $admin->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this admin?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Delete Admin" class="text-red-500 hover:text-red-600 p-2 rounded-lg hover:bg-red-100 transition duration-150 ease-in-out">
                                                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100" viewBox="0 0 32 32" class="w-6 h-6 md:w-7 md:h-7" >
                                                    <path fill="#ef4444" fill-rule="nonzero" stroke-linecap="round" stroke-linejoin="round" d="M 15 4 C 14.476563 4 13.941406 4.183594 13.5625 4.5625 C 13.183594 4.941406 13 5.476563 13 6 L 13 7 L 7 7 L 7 9 L 8 9 L 8 25 C 8 26.644531 9.355469 28 11 28 L 23 28 C 24.644531 28 26 26.644531 26 25 L 26 9 L 27 9 L 27 7 L 21 7 L 21 6 C 21 5.476563 20.816406 4.941406 20.4375 4.5625 C 20.058594 4.183594 19.523438 4 19 4 Z M 15 6 L 19 6 L 19 7 L 15 7 Z M 10 9 L 24 9 L 24 25 C 24 25.554688 23.554688 26 23 26 L 11 26 C 10.445313 26 10 25.554688 10 25 Z M 12 12 L 12 23 L 14 23 L 14 12 Z M 16 12 L 16 23 L 18 23 L 18 12 Z M 20 12 L 20 23 L 22 23 L 22 12 Z"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach

                            {{-- Add New Admin Row --}}
                            <tr>
                                <form method="POST" action="{{ route('admin.access-control.store') }}" class="grid grid-cols-4 gap-2 md:gap-4">
                                    @csrf
                                    <td class="px-2 py-4 text-xs md:text-sm">
                                        <input type="text" name="name" placeholder="Name" class="border border-gray-300 rounded-md p-1 text-sm w-full" required>
                                    </td>
                                    <td class="px-2 py-4 text-xs md:text-sm">
                                        <input type="email" name="email" placeholder="Email" class="border border-gray-300 rounded-md p-1 text-sm w-full" required>
                                    </td>
                                    <td class="px-2 py-4 text-xs md:text-sm">
                                        {{ now()->format('Y-m-d') }}
                                    </td>
                                    <td class="px-2 py-4 text-xs md:text-sm">
                                        <button type="submit" class="p-2 bg-blue-700 hover:bg-blue-600 ml-2 text-white text-xs font-semibold rounded-lg  justify-center items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </td>
                                </form>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
