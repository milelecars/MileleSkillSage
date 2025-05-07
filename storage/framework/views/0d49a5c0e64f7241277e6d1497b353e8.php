<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
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
                                <form method="GET" action="<?php echo e(route('admin.manage-candidates')); ?>" class="space-y-3">
                                    <div>
                                        <label for="test_filter" class="block text-xs font-medium text-gray-700">Test</label>
                                        <select name="test_filter" id="test_filter" class="w-full border-gray-300 rounded-md mt-1 placeholder:text-xs md:placeholder:text-sm">
                                            <option value="" class="placeholder:text-xs md:placeholder:text-sm">All Tests</option>
                                            <?php $__currentLoopData = $availableTests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $test): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($test->id); ?>" <?php echo e($testFilter == $test->id ? 'selected' : ''); ?>><?php echo e($test->title); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>

                                    <!-- Name Filter -->
                                    <div>
                                        <label for="name" class="block text-xs font-medium text-gray-700">Name</label>
                                        <input type="text" name="name" id="name" value="<?php echo e(request('name')); ?>" placeholder="Search by name" class="placeholder:text-xs md:placeholder:text-sm w-full border-gray-300 rounded-md mt-1">
                                    </div>

                                    <!-- Email Filter -->
                                    <div>
                                        <label for="email" class="block text-xs font-medium text-gray-700">Email</label>
                                        <input type="text" name="email" id="email" value="<?php echo e(request('email')); ?>" placeholder="Search by email" class="placeholder:text-xs md:placeholder:text-sm w-full border-gray-300 rounded-md mt-1">
                                    </div>

                                    <!-- Role Filter -->
                                    <div>
                                        <label for="role" class="block text-xs font-medium text-gray-700">Role</label>
                                        <input type="text" name="role" id="role" value="<?php echo e(request('role')); ?>" placeholder="Search by role" class="placeholder:text-xs md:placeholder:text-sm w-full border-gray-300 rounded-md mt-1">
                                    </div>

                                    <!-- Department Filter -->
                                    <div>
                                        <label for="department" class="block text-xs font-medium text-gray-700">Department</label>
                                        <select name="department" id="department" class="w-full border-gray-300 rounded-md mt-1 placeholder:text-xs md:placeholder:text-sm">
                                            <option value="">All Departments</option>
                                            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($dept->name); ?>" <?php echo e(request('department') === $dept->name ? 'selected' : ''); ?>>
                                                    <?php echo e($dept->name); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex justify-between items-center pt-5">
                                        <a href="<?php echo e(route('admin.export-candidates', request()->only(['search', 'test_filter', 'name', 'email', 'role', 'department']))); ?>"
                                           class="inline-flex items-center bg-green-600 text-white text-xs md:text-sm px-3 py-2 rounded-md hover:bg-green-700">

                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Export
                                        </a>

                                        <button type="submit" class="bg-blue-600 text-white px-3 py-2 rounded-md text-xs md:text-sm">Search</button>

                                        <a href="<?php echo e(route('admin.manage-candidates')); ?>" class="bg-gray-600 text-white px-3 py-2 rounded-md text-xs md:text-sm">Clear</a>

                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-2 text-center items-center md:grid-cols-4 gap-4 mb-6 relative">
                        <div class="bg-blue-50 p-4 rounded-lg gap-2 h-full flex flex-col justify-between">
                            <h3 class="text-base md:text-lg font-semibold text-blue-700">Active</h3>
                            <p class="text-lg md:text-2xl font-bold text-blue-900"><?php echo e($activeTests); ?></p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg gap-2 h-full flex flex-col justify-between">
                            <h3 class="text-base md:text-lg font-semibold text-green-700">Invited</h3>
                            <p class="text-lg md:text-2xl font-bold text-green-900"><?php echo e($totalInvited); ?></p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg gap-2 h-full flex flex-col justify-between">
                            <h3 class="text-base md:text-lg font-semibold text-purple-700">Completed</h3>
                            <p class="text-lg md:text-2xl font-bold text-purple-900"><?php echo e($completedTestsCount); ?></p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg gap-2 h-full flex flex-col justify-between">
                            <h3 class="text-base md:text-lg font-semibold text-gray-700">Tests</h3>
                            <p class="text-lg md:text-2xl font-bold text-gray-900"><?php echo e($totalTests); ?></p>
                        </div>
                    </div>
                    
                    <?php if(session('error')): ?>
                        <div class="mb-4 text-sm text-red-600 bg-red-100 border border-red-400 p-3 rounded-md">
                            <?php echo e(session('error')); ?>

                        </div>
                    <?php endif; ?>

                    <?php if(session('success')): ?>
                        <div class="mb-4 text-sm text-green-600 bg-green-100 border border-green-400 p-3 rounded-md">
                            <?php echo e(session('success')); ?>

                        </div>
                    <?php endif; ?>

                    <?php if($errors->any()): ?>
                        <div class="mb-4 text-sm text-red-600 bg-red-100 border border-red-400 p-3 rounded-md">
                            <ul class="list-disc pl-5">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="overflow-x-auto rounded-lg ">
                        <div class="max-h-[70vh] table-fixed overflow-y-auto border border-gray-200">
                            <table class="min-w-full divide-gray-200" id="candidatesTable">
                                <thead class="bg-gray-100 sticky top-0 z-10">
                                    <tr>
                                        <th class="md:w-[100px] px-2 md:px-0 py-1 md:py-3 text-xs font-semibold text-gray-500 uppercase cursor-pointer" data-sort="candidate">
                                            <div class="flex items-center justify-center">
                                                Candidate
                                                <button class="sort-icon ml-1">⇅</button>
                                            </div>
                                        </th>
                                        <th class="md:w-[140px] px-2 md:px-0 py-1 md:py-3 text-xs font-semibold text-gray-500 uppercase cursor-pointer" data-sort="test">
                                            <div class="flex items-center justify-center">
                                                Test
                                                <button class="sort-icon ml-1">⇅</button>
                                            </div>
                                        </th>
                                        <th class="md:w-[120px] px-2 md:px-0 py-1 md:py-3 text-xs font-semibold text-gray-500 uppercase cursor-pointer" data-sort="role">
                                            <div class="flex items-center justify-center">
                                                Role
                                                <button class="sort-icon ml-1">⇅</button>
                                            </div>
                                        </th>
                                        <th class="md:w-[130px] px-2 md:px-0 py-1 md:py-3 text-xs font-semibold text-gray-500 uppercase cursor-pointer" data-sort="department">
                                            <div class="flex items-center justify-center">
                                                Department
                                                <button class="sort-icon ml-1">⇅</button>
                                            </div>
                                        </th>
                                        <th class="md:w-[120px] px-2 md:px-0 py-1 md:py-3 text-xs font-semibold text-gray-500 uppercase cursor-pointer" data-sort="status">
                                            <div class="flex items-center justify-center">
                                                Status
                                                <button class="sort-icon ml-1">⇅</button>
                                            </div>
                                        </th>
                                        <th class="md:w-[160px] px-2 md:px-0 py-1 md:py-3 text-xs font-semibold text-gray-500 uppercase cursor-pointer" data-sort="started">
                                            <div class="px-2 flex items-center justify-center">
                                                Started At
                                                <button class="sort-icon ml-1">⇅</button>
                                            </div>
                                        </th>
                                        <th class="md:w-[160px] px-2 md:px-0 py-1 md:py-3 text-xs font-semibold text-gray-500 uppercase cursor-pointer" data-sort="completed">
                                            <div class="px-2 flex items-center justify-center">
                                                Completed At
                                                <button class="sort-icon ml-1">⇅</button>
                                            </div>
                                        </th>
                                        <th class="md:w-[80px] px-2 md:px-0 py-1 md:py-3 text-xs font-semibold text-gray-500 uppercase cursor-pointer" data-sort="score">
                                            <div class="flex items-center justify-center">
                                                Score
                                                <button class="sort-icon ml-1">⇅</button>
                                            </div>
                                        </th>
                                        <th class="md:w-[120px] px-2 md:px-0 py-1 md:py-3 text-xs font-semibold text-gray-500 uppercase cursor-pointer" data-sort="percentile">
                                            <div class="flex items-center justify-center">
                                                Percentile
                                                <button class="sort-icon ml-1">⇅</button>
                                            </div>
                                        </th>
                                        <th class="md:w-[70px] px-2 md:px-0 py-1 md:py-3 text-xs font-semibold text-gray-500 uppercase">
                                            Report
                                        </th>
                                        <th class="md:w-[90px] px-2 md:px-0 py-1 md:py-3 text-xs font-semibold text-gray-500 uppercase">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="bg-white divide-y divide-gray-200 text-center">
                                    <?php $__empty_1 = true; $__currentLoopData = $candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr class="relative items-center justify-center" 
                                            data-candidate="<?php echo e($candidate['name'] ?? $candidate['email']); ?>" 
                                            data-test="<?php echo e($candidate['test_title']); ?>" 
                                            data-role="<?php echo e($candidate['role'] ?? '-'); ?>"
                                            data-department="<?php echo e($candidate['department'] ?? '-'); ?>"
                                            data-department="<?php echo e($candidate['department'] ?? '-'); ?>"
                                            data-status="<?php echo e($candidate['status']); ?>"
                                            data-started="<?php echo e(isset($candidate['started_at']) ? \Carbon\Carbon::parse($candidate['started_at'])->format('Y-m-d H:i:s') : '0'); ?>"
                                            data-completed="<?php echo e(isset($candidate['completed_at']) ? \Carbon\Carbon::parse($candidate['completed_at'])->format('Y-m-d H:i:s') : '0'); ?>"
                                            data-score="<?php echo e(isset($candidate['score']) ? $candidate['score'] : '0'); ?>"
                                            data-percentile="<?php echo e(isset($candidate['percentile']) ? $candidate['percentile'] : '0'); ?>">
                                            <td class="py-4 h-full">
                                                <?php if($candidate['has_started']): ?>
                                                    <a href="<?php echo e(route('admin.candidate-result', ['test' => $candidate['test_id'], 'candidate' => $candidate['id']])); ?>" class="hover:text-blue-600">
                                                        <div class="text"><?php echo e($candidate['name']); ?></div>
                                                        <div class="text-xs text-gray-500"><?php echo e($candidate['email']); ?></div>
                                                    </a>
                                                <?php else: ?>
                                                    <div class="text-xs text-gray-500"><?php echo e($candidate['email']); ?></div>
                                                <?php endif; ?>
                                            </td>

                                            <td class="py-4 h-full text-xs md:text-sm"><?php echo e($candidate['test_title']); ?></td>

                                            <td class="py-4 h-full text-xs md:text-sm"><?php echo e($candidate['role'] ?? "-"); ?></td>

                                            <td class="py-4 h-full text-xs md:text-sm"><?php echo e($candidate['department'] ?? "-"); ?></td>

                                            
                                            <td class="py-4 h-full text-xs md:text-sm">
                                                <?php if($candidate['status'] === 'accepted'): ?>
                                                    <span class="text-green-800 bg-green-100 px-2 py-1 rounded-full">Accepted</span>
                                                <?php elseif($candidate['status'] === 'rejected'): ?>
                                                    <span class="text-red-800 bg-red-100 px-2 py-1 rounded-full">Rejected</span>
                                                <?php elseif($candidate['status'] === 'completed'): ?>
                                                    <span class="text-blue-800 bg-blue-100 px-2 py-1 rounded-full">Completed</span>
                                                <?php elseif($candidate['status'] === 'in_progress'): ?>
                                                    <span class="text-yellow-800 bg-yellow-100 px-2 py-1 rounded-full">In Progress</span>
                                                <?php elseif($candidate['status'] === 'suspended'): ?>
                                                    <span class="text-orange-800 bg-orange-100 px-2 py-1 rounded-full">Suspended</span>
                                                <?php elseif($candidate['status'] === 'expired'): ?>
                                                    <span class="text-red-800 bg-red-100 px-2 py-1 rounded-full">Expired</span>
                                                <?php else: ?>
                                                    <span class="text-gray-800 bg-gray-100 px-2 py-1 rounded-full">Not Started</span>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <td class="py-4 h-full text-xs">
                                                <?php echo e(isset($candidate['started_at']) ? \Carbon\Carbon::parse($candidate['started_at'])->format('M d, Y H:i') : '-'); ?>

                                            </td>
                                            <td class="py-4 h-full text-xs">
                                                <?php echo e(isset($candidate['completed_at']) ? \Carbon\Carbon::parse($candidate['completed_at'])->format('M d, Y H:i') : '-'); ?>

                                            </td>
                                            <td class="py-4 h-full text-xs md:text-sm">
                                                <?php if(isset($candidate['score'])): ?>
                                                    <span class="font-medium">
                                                    <?php echo e($candidate['score']); ?><?php echo e($candidate['hasMCQ'] ? '%' : ''); ?>

                                                    </span>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            
                                            <td class="py-4 h-full text-xs md:text-sm">
                                                <?php if(isset($candidate['percentile'])): ?>
                                                    <?php if($candidate['percentile'] >= 99): ?>
                                                        Top 1%
                                                    <?php elseif($candidate['percentile'] > 0): ?>
                                                        Top <?php echo e(100 - floor($candidate['percentile'])); ?>%
                                                    <?php else: ?>
                                                        Bottom Performer
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            
                                            <td class="py-6 h-full">
                                                <?php if(isset($candidate['completed_at'])): ?>
                                                    <a class="flex items-center justify-center" href="<?php echo e(route('reports.candidate-report', ['candidateId' => $candidate['id'], 'testId' => $candidate['test_id']])); ?>">
                                                        <svg fill="#102141" width="25px" height="25px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="#102141" stroke-width="0.00024000000000000003"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="m20 8-6-6H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM9 19H7v-9h2v9zm4 0h-2v-6h2v6zm4 0h-2v-3h2v3zM14 9h-1V4l5 5h-4z"></path></g></svg>
                                                    </a>
                                                <?php else: ?>
                                                    <span>-</span>
                                                <?php endif; ?>
                                            </td>

                                            <td class="py-6 h-full">
                                                <div class="relative flex items-center justify-center" x-data="{ open: false, showDeadlineModal: false }">
                                                    <?php if($candidate['status'] === 'completed' || ($candidate['status'] === 'suspended' && $candidate['unsuspend_count'] < 1)): ?>
                                                        <button @click="open = !open" class="text-gray-600 hover:text-gray-800">
                                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                                            </svg>
                                                        </button>
                                                        
                                                        <div x-show="open" 
                                                            @click.away="open = false" 
                                                            class="absolute right-0 mt-2 w-36 bg-white rounded-md shadow-lg py-1 z-50">
                                                            
                                                            <?php if($candidate['status'] === 'suspended' && $candidate['unsuspend_count'] < 1): ?>
                                                                <form action="<?php echo e(route('admin.unsuspend-test', [$candidate['id'], $candidate['test_id']])); ?>" method="POST" class="block"
                                                                    onsubmit="return confirm('Are you sure you want to unsuspend this test?');">
                                                                    <?php echo csrf_field(); ?>
                                                                    <button type="submit" class="w-full text-left px-4 py-2 text-xs md:text-sm text-orange-600 hover:bg-gray-100">
                                                                        Unsuspend Test
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>

                                                            <?php if($candidate['status'] === 'completed'): ?>
                                                                <form action="<?php echo e(route('candidate.accept', $candidate['id'])); ?>" method="POST" class="block"
                                                                    onsubmit="return confirm('Are you sure you want to accept this candidate?');">
                                                                    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                                                                    <input type="hidden" name="test_id" value="<?php echo e($candidate['test_id']); ?>">
                                                                    <button type="submit" class="w-full text-left px-4 py-2 text-xs md:text-sm text-green-700 hover:bg-gray-100">
                                                                        Accept
                                                                    </button>
                                                                </form>

                                                                <form action="<?php echo e(route('candidate.reject', $candidate['id'])); ?>" method="POST" class="block"
                                                                    onsubmit="return confirm('Are you sure you want to reject this candidate?');">
                                                                    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                                                                    <input type="hidden" name="test_id" value="<?php echo e($candidate['test_id']); ?>">
                                                                    <button type="submit" class="w-full text-left px-4 py-2 text-xs md:text-sm text-orange-600 hover:bg-gray-100">
                                                                        Reject
                                                                    </button>
                                                                </form>

                                                                <form action="<?php echo e(route('candidate.delete', [$candidate['id'], $candidate['test_id']])); ?>" method="POST" class="block"
                                                                    onsubmit="return confirm('Are you sure you want to delete this candidate?');">
                                                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                                                    <button type="submit" class="w-full text-left px-4 py-2 text-xs md:text-sm text-red-600 hover:bg-gray-100">
                                                                        Delete
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>

                                                        </div>
                                                    <?php elseif($candidate['status'] === 'expired'): ?>
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
                                                                    <span class="block font-medium"><?php echo e(\Carbon\Carbon::parse($candidate['expiration_date'])->format('M d, Y H:i')); ?></span>
                                                                </div>
                                                                
                                                                <form action="<?php echo e(route('invitations.extend-deadline')); ?>" method="POST" onsubmit="return validateDeadline()">
                                                                    <?php echo csrf_field(); ?>
                                                                    <input type="hidden" name="test_id" value="<?php echo e($candidate['test_id']); ?>">
                                                                    <input type="hidden" name="email" value="<?php echo e($candidate['email']); ?>">
                                                                    
                                                                    <div class="flex gap-2 items-center mb-4">
                                                                        <label class="text-gray-600">New Deadline:</label>
                                                                        <input type="datetime-local" 
                                                                            id="new_deadline"
                                                                            name="new_deadline" 
                                                                            class="border border-gray-300 rounded-md p-2"
                                                                            min="<?php echo e(now()->format('Y-m-d\TH:i')); ?>"
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
                                                    <?php else: ?>
                                                        <span class="text-gray-400">-</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="9" class="text-center py-4">No candidates found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>

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

</script><?php /**PATH C:\Users\HeliaHaghighi\Desktop\MileleSkillSage\resources\views/admin/manage-candidates.blade.php ENDPATH**/ ?>