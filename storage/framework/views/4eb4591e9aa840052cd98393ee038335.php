<div>
    <div class="overflow-x-auto rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 border border-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-2 py-3 text-xs font-semibold text-gray-500 uppercase w-1/5">First Name</th>
                    <th class="px-2 py-3 text-xs font-semibold text-gray-500 uppercase w-1/5">Last Name</th>
                    <th class="px-2 py-3 text-xs font-semibold text-gray-500 uppercase w-1/5">Role</th>
                    <th class="px-2 py-3 text-xs font-semibold text-gray-500 uppercase w-1/5">Email</th>
                    <th class="px-2 py-3 text-xs font-semibold text-gray-500 uppercase w-1/5">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-center">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $emailList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="px-2 py-4 text-sm"><?php echo e($record['firstName']); ?></td>
                        <td class="px-2 py-4 text-sm"><?php echo e($record['lastName']); ?></td>
                        <td class="px-2 py-4 text-sm"><?php echo e($record['role']); ?></td>
                        <td class="px-2 py-4 text-sm"><?php echo e($record['email']); ?></td>
                        <td class="px-2 py-4">
                            <button 
                                wire:click="removeEmail(<?php echo e($index); ?>)"
                                class="bg-red-600 text-white text-xs font-bold px-4 py-2 rounded-lg hover:bg-red-700">
                                -
                            </button>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->

                <!-- Add New Row -->
                <tr>
                    <td colspan="5" class="px-2 py-4">
                        <form wire:submit.prevent="addEmail" class="grid grid-cols-5 gap-2 items-center justify-center">
                            <div class="flex flex-col">
                                <input type="text" 
                                    wire:model="firstName" 
                                    class="border border-gray-300 rounded-md p-2 text-sm"
                                    name="firstName"
                                >
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['firstName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                    <span class="text-red-500 text-xs mt-1"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            
                            <div class="flex flex-col">
                                <input type="text" 
                                    wire:model="lastName" 
                                    class="border border-gray-300 rounded-md p-2 text-sm"
                                    name="lastName"
                                >
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['lastName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                    <span class="text-red-500 text-xs mt-1"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            
                            <div class="flex flex-col">
                                <input type="text" 
                                    wire:model="role" 
                                    class="border border-gray-300 rounded-md p-2 text-sm"
                                    name="role"
                                >
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                    <span class="text-red-500 text-xs mt-1"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            
                            <div class="flex flex-col">
                                <input type="email" 
                                    wire:model="newEmail" 
                                    class="border border-gray-300 rounded-md p-2 text-sm"
                                    name="newEmail"
                                >
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['newEmail'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                    <span class="text-red-500 text-xs mt-1"><?php echo e($message); ?></span>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>

                            <div class="p-2">
                                <button type="submit" 
                                    class="bg-blue-700 hover:bg-blue-600 ml-2 text-white text-xs font-semibold px-[9px] py-1.5 rounded-lg"
                                    wire:loading.attr="disabled"
                                    wire:target="addEmail">
                                    <span wire:loading.remove wire:target="addEmail">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                    <span wire:loading wire:target="addEmail">
                                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                </button>
                            </div>
                        </form>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!--[if BLOCK]><![endif]--><?php if($emailList): ?>
        <div class="my-6">
            <button wire:click="submitInvitations" 
                    wire:loading.attr="disabled"
                    wire:target="submitInvitations"
                    class="bg-green-600 hover:bg-green-500 text-white font-bold py-2 px-4 rounded-lg inline-flex items-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="submitInvitations">
                    Send Invitations
                </span>
                <span wire:loading wire:target="submitInvitations" class="flex items-center justify-between">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Sending...
                </span>
            </button>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!--[if BLOCK]><![endif]--><?php if(session('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 p-4 rounded-lg">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!--[if BLOCK]><![endif]--><?php if(session('warning')): ?>
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 p-4 rounded-lg">
            <?php echo e(session('warning')); ?>

        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <!--[if BLOCK]><![endif]--><?php if($errors->has('submission')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 p-4 rounded-lg">
            <?php echo e($errors->first('submission')); ?>

        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

</div><?php /**PATH C:\Users\HeliaHaghighi\Desktop\MileleSkillSage\resources\views/livewire/invite-candidates.blade.php ENDPATH**/ ?>