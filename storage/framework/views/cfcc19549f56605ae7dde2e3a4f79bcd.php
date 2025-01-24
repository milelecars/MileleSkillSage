<div>
    
    <div class="mb-4">
        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
        <input 
            id="email" 
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
    </div>


    
    <div class="mb-1">
        <label for="otp" class="block text-gray-700 text-sm font-bold mb-2">OTP</label>
        <div class="flex">
            <input 
                id="otp" 
                type="text" 
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
</div>
<?php /**PATH C:\Users\HeliaHaghighi\Desktop\MileleSkillSage\resources\views/livewire/login-field.blade.php ENDPATH**/ ?>