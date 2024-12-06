<x-app-layout>
    <div class="py-12 text-theme">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-800">
                        Welcome, {{ Auth::guard('web')->user()->name}} !
                    </h1>
                    <div class="text-sm text-gray-500 mt-1">
                        {{ Auth::guard('web')->user()->email }}
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="{{ route('tests.index') }}" class="block p-6 bg-blue-100 hover:bg-blue-200 rounded-lg transition duration-300">
                            <h3 class="text-lg font-semibold text-blue-700">Manage Tests</h3>
                            <p class="text-blue-600 mt-2">View, create, and edit assessment tests</p>
                        </a>
                        <a href="{{ route('admin.manage-candidates') }}" class="block p-6 bg-green-100 hover:bg-green-200 rounded-lg transition duration-300">
                            <h3 class="text-lg font-semibold text-green-700">Manage Candidates</h3>
                            <p class="text-green-600 mt-2">View test results, approve/reject candidates</p>
                        </a>
                        <a href="{{ route('admin.manage-reports') }}" class="block p-6 bg-purple-100 hover:bg-purple-200 rounded-lg transition duration-300">
                            <h3 class="text-lg font-semibold text-purple-700">Reports</h3>
                            <p class="text-purple-600 mt-2">Generate and manage reports</p>
                        </a>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>