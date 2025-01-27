
<form method="POST" action="<?php echo e(route('login')); ?>" class="w-full max-w-sm mx-auto my-2">
    <?php echo csrf_field(); ?>


    <!--[if BLOCK]><![endif]--><?php if(session('success')): ?>
        <div class="alert alert-success mb-4 text-green-500">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    
    <div class="mb-4">
        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
        <input 
            id="email" 
            name="email"
            type="email" 
            wire:model="email" 
            required 
            autocomplete="username" 
            class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
        >
    </div>

    <div class="mb-6">
        <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
        <input 
            id="password" 
            type="password" 
            name="password" 
            required 
            autocomplete="current-password" 
            class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
        >
        <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
    </div>


    
    <div class="mb-1">
        <label for="otp" class="block text-gray-700 text-sm font-bold mb-2">OTP</label>
        <div class="flex">
            <input 
                id="otp" 
                type="text" 
                name="OTP"
                wire:model="otp" 
                placeholder="Enter OTP sent to your email"
                required 
                autocomplete="one-time-code" 
                class="appearance-none border text-sm rounded-l-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            >
            <button 
                type="button" 
                wire:click="generateOtp" 
                class="text-white border border-neutral-500 bg-theme font-bold px-3 rounded-r-lg focus:outline-none focus:shadow-outline text-xs flex items-center">
                <span><?php echo e($otpGenerated ? 'Resend' : 'Generate'); ?></span>
            </button>
        </div>
    </div>

    
    <!--[if BLOCK]><![endif]--><?php if($errorMessage): ?>
        <p class="text-red-500 text-xs"><?php echo e($errorMessage); ?></p>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    <!--[if BLOCK]><![endif]--><?php if($successMessage): ?>
        <p class="text-green-500 text-xs"><?php echo e($successMessage); ?></p>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->



    
    <div class="flex items-center justify-between mt-7">
        <button type="submit" class="bg-blue-700 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Log in
        </button>
        
        <a href="<?php echo e(route('register')); ?>" class="inline-block align-baseline font-bold text-sm text-theme hover:text-blue-600">
            Need an account?
        </a>
    </div>
</from><?php /**PATH C:\Users\HeliaHaghighi\Desktop\MileleSkillSage\resources\views/livewire/login-field.blade.php ENDPATH**/ ?>